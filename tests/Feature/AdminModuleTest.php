<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\Department;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::where('role_id', User::ROLE_ADMIN)->first();
    }

    public function test_guests_cannot_access_admin_pages(): void
    {
        $this->get('/admin/users')->assertRedirect('/login');
        $this->get('/admin/areas')->assertRedirect('/login');
        $this->get('/admin/categories')->assertRedirect('/login');
        $this->get('/admin/reports')->assertRedirect('/login');
        $this->get('/admin/logs')->assertRedirect('/login');
    }

    public function test_admin_can_view_users_index(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/users');
        $response->assertStatus(200);
        $response->assertSee('Manage Users');
        $response->assertSee($this->admin->email);
    }

    public function test_complainant_registration_requires_document_and_queues_for_approval(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');

        $area = Area::first();
        $file = \Illuminate\Http\UploadedFile::fake()->create('valid_id.pdf', 500, 'application/pdf');

        $response = $this->post('/register', [
            'first_name' => 'Kiko',
            'last_name' => 'Pangilinan',
            'email' => 'kiko@example.local',
            'contact_number' => '09179998877',
            'area_id' => $area->area_id,
            'address' => 'Block 1 Lot 2 Banlic',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'verification_doc_type' => 'valid_id',
            'verification_doc' => $file,
            'privacy' => '1',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('success');

        $user = User::where('email', 'kiko@example.local')->first();
        $this->assertNotNull($user);
        $this->assertEquals(User::STATUS_PENDING, $user->verification_status);
        $this->assertFalse($user->is_active);
        $this->assertNotNull($user->verification_doc_path);

        // Pending user cannot log in
        $loginAttempt = $this->post('/login', [
            'email' => 'kiko@example.local',
            'password' => 'Password123!',
        ]);
        $loginAttempt->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_can_view_pending_queue_and_approve_applicant(): void
    {
        $user = User::create([
            'role_id' => User::ROLE_COMPLAINANT,
            'first_name' => 'Apolinario',
            'last_name' => 'Mabini',
            'email' => 'mabini@example.local',
            'password_hash' => bcrypt('Password123!'),
            'verification_status' => User::STATUS_PENDING,
            'verification_doc_type' => 'voters_cert',
            'is_active' => false,
        ]);

        // View queue
        $queueResp = $this->actingAs($this->admin)->get('/admin/users/pending');
        $queueResp->assertStatus(200);
        $queueResp->assertSee('Apolinario Mabini');
        $queueResp->assertSee("Voter's Certification");

        // Approve user
        $approveResp = $this->actingAs($this->admin)->post("/admin/users/{$user->user_id}/approve");
        $approveResp->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals(User::STATUS_APPROVED, $user->verification_status);
        $this->assertTrue($user->is_active);
        $this->assertEquals($this->admin->user_id, $user->verified_by);

        // User can now log in
        $this->post('/logout');
        $login = $this->post('/login', [
            'email' => 'mabini@example.local',
            'password' => 'Password123!',
        ]);
        $login->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_reject_pending_applicant(): void
    {
        $user = User::create([
            'role_id' => User::ROLE_COMPLAINANT,
            'first_name' => 'Emilio',
            'last_name' => 'Aguinaldo',
            'email' => 'emilio@example.local',
            'password_hash' => bcrypt('Password123!'),
            'verification_status' => User::STATUS_PENDING,
            'verification_doc_type' => 'certificate_of_residency',
            'is_active' => false,
        ]);

        $rejectResp = $this->actingAs($this->admin)->post("/admin/users/{$user->user_id}/reject", [
            'rejection_reason' => 'Blurred document image',
        ]);
        $rejectResp->assertSessionHas('warning');

        $user->refresh();
        $this->assertEquals(User::STATUS_REJECTED, $user->verification_status);
        $this->assertFalse($user->is_active);
        $this->assertEquals('Blurred document image', $user->rejection_reason);
    }

    public function test_admin_can_create_a_new_user(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/users', [
            'role_id' => User::ROLE_COMPLAINANT,
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => 'juan@example.local',
            'password' => 'Secret123!',
            'contact_number' => '09171234567',
            'is_active' => '1',
        ]);

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', ['email' => 'juan@example.local']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'CREATE', 'table_name' => 'users']);
    }

    public function test_admin_can_update_user(): void
    {
        $user = User::create([
            'role_id' => User::ROLE_COMPLAINANT,
            'first_name' => 'Maria',
            'last_name' => 'Clara',
            'email' => 'maria@example.local',
            'password_hash' => bcrypt('password'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->put("/admin/users/{$user->user_id}", [
            'role_id' => User::ROLE_COMPLAINANT,
            'first_name' => 'Maria Updated',
            'last_name' => 'Clara',
            'email' => 'maria@example.local',
            'contact_number' => '09181234567',
            'is_active' => '1',
        ]);

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', ['first_name' => 'Maria Updated']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'UPDATE', 'table_name' => 'users']);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $response = $this->actingAs($this->admin)->delete("/admin/users/{$this->admin->user_id}");
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['user_id' => $this->admin->user_id]);
    }

    public function test_admin_can_create_and_update_departments_and_areas(): void
    {
        // Add Area
        $resArea = $this->actingAs($this->admin)->post('/admin/areas', [
            'area_name' => 'Purok 7 Test',
            'description' => 'Test Area description',
        ]);
        $resArea->assertSessionHas('success');
        $this->assertDatabaseHas('areas', ['area_name' => 'Purok 7 Test']);

        // Add Department
        $resDept = $this->actingAs($this->admin)->post('/admin/departments', [
            'department_name' => 'Test Department',
            'description' => 'Test Dept description',
        ]);
        $resDept->assertSessionHas('success');
        $this->assertDatabaseHas('departments', ['department_name' => 'Test Department']);
    }

    public function test_admin_can_create_and_manage_categories(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/categories', [
            'category_name' => 'Illegal Parking',
            'description' => 'Vehicles blocking the roadway',
        ]);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('complaint_categories', ['category_name' => 'Illegal Parking']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'CREATE', 'table_name' => 'complaint_categories']);
    }

    public function test_admin_can_view_and_export_reports(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/reports');
        $response->assertStatus(200);
        $response->assertSee('Reports & Analytics');

        // CSV export
        $export = $this->actingAs($this->admin)->get('/admin/reports/export');
        $export->assertStatus(200);
        $this->assertTrue(str_contains($export->headers->get('content-type') ?? '', 'text/csv'));

        // Print view
        $print = $this->actingAs($this->admin)->get('/admin/reports/print');
        $print->assertStatus(200);
        $print->assertSee('OFFICIAL INCIDENTS AND COMPLAINTS SUMMARY REPORT');
    }

    public function test_admin_can_view_audit_logs(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/logs');
        $response->assertStatus(200);
        $response->assertSee('Audit Logs');
    }

    public function test_admin_can_view_all_complaints_list_and_details(): void
    {
        $resident = User::firstOrCreate(
            ['email' => 'resident_test@example.local'],
            [
                'role_id' => User::ROLE_COMPLAINANT,
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'password_hash' => bcrypt('secret'),
                'is_active' => true,
            ]
        );

        $complaint = Complaint::create([
            'reference_no' => 'BRGY-2026-99999',
            'complainant_id' => $resident->user_id,
            'report_type_id' => 1,
            'category_id' => 1,
            'priority_id' => 2,
            'area_id' => 1,
            'status_id' => 1,
            'title' => 'Loud Karaoke at 2 AM',
            'description' => 'Neighbor is playing loud karaoke past curfew hours continuously.',
        ]);

        // List complaints
        $listResp = $this->actingAs($this->admin)->get('/admin/complaints');
        $listResp->assertStatus(200);
        $listResp->assertSee('Loud Karaoke at 2 AM');
        $listResp->assertSee('BRGY-2026-99999');

        // View single complaint detail (Admin View)
        $detailResp = $this->actingAs($this->admin)->get("/admin/complaints/{$complaint->reference_no}");
        $detailResp->assertStatus(200);
        $detailResp->assertSee('Assign to Barangay Officer');
        $detailResp->assertSee('Update Status & Priority');
    }

    public function test_admin_can_assign_officer_and_update_status(): void
    {
        $resident = User::firstOrCreate(
            ['email' => 'resident_test2@example.local'],
            [
                'role_id' => User::ROLE_COMPLAINANT,
                'first_name' => 'Jose',
                'last_name' => 'Rizal',
                'password_hash' => bcrypt('secret'),
                'is_active' => true,
            ]
        );

        $complaint = Complaint::create([
            'reference_no' => 'BRGY-2026-88888',
            'complainant_id' => $resident->user_id,
            'report_type_id' => 1,
            'category_id' => 1,
            'priority_id' => 2,
            'area_id' => 1,
            'status_id' => 1, // Pending
            'title' => 'Garbage pile near creek',
            'description' => 'Uncollected waste piling up near the water stream.',
        ]);

        // Assign to admin officer
        $assignResp = $this->actingAs($this->admin)->post("/admin/complaints/{$complaint->reference_no}/assign", [
            'assigned_to' => $this->admin->user_id,
            'remarks' => 'Please inspect this morning',
        ]);
        $assignResp->assertSessionHas('success');

        $complaint->refresh();
        $this->assertEquals(3, $complaint->status_id); // Auto-moves to Assigned
        $this->assertNotNull($complaint->currentAssignment);
        $this->assertEquals($this->admin->user_id, $complaint->currentAssignment->assigned_to);

        // Update status to In Progress (ID: 4)
        $statusResp = $this->actingAs($this->admin)->put("/admin/complaints/{$complaint->reference_no}/status", [
            'status_id' => 4,
            'priority_id' => 3, // High
            'remarks' => 'Inspection underway with tanods',
        ]);
        $statusResp->assertSessionHas('success');

        $complaint->refresh();
        $this->assertEquals(4, $complaint->status_id);
        $this->assertEquals(3, $complaint->priority_id);
    }

    public function test_complaints_quick_filter_pills_and_overdue_indicator(): void
    {
        $resident = User::firstOrCreate(
            ['email' => 'resident_pill@example.local'],
            [
                'role_id' => User::ROLE_COMPLAINANT,
                'first_name' => 'Clara',
                'last_name' => 'Reyes',
                'password_hash' => bcrypt('secret'),
                'is_active' => true,
            ]
        );

        // Complaint created 3 days ago (overdue)
        $staleComplaint = Complaint::create([
            'reference_no' => 'BRGY-2026-77777',
            'complainant_id' => $resident->user_id,
            'report_type_id' => 1,
            'category_id' => 1,
            'priority_id' => 1,
            'area_id' => 1,
            'status_id' => 1, // Pending
            'title' => 'Stale complaint overdue test',
            'description' => 'Waiting for over 48 hours.',
            'created_at' => now()->subHours(72),
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/complaints?tab=needs_review');
        $response->assertStatus(200);
        $response->assertSee('Needs Review');
        $response->assertSee('Overdue (&gt;48h)', false);
        $response->assertSee('BRGY-2026-77777');
    }

    public function test_public_tracking_masks_complainant_name(): void
    {
        $resident = User::firstOrCreate(
            ['email' => 'resident_mask@example.local'],
            [
                'role_id' => User::ROLE_COMPLAINANT,
                'first_name' => 'Rodrigo',
                'last_name' => 'Mendoza',
                'password_hash' => bcrypt('secret'),
                'is_active' => true,
            ]
        );

        $complaint = Complaint::create([
            'reference_no' => 'BRGY-2026-66666',
            'complainant_id' => $resident->user_id,
            'report_type_id' => 1,
            'category_id' => 1,
            'priority_id' => 2,
            'area_id' => 1,
            'status_id' => 2,
            'title' => 'Noise disturbance',
            'description' => 'Very loud music at night.',
        ]);

        $response = $this->get('/track?reference=BRGY-2026-66666');
        $response->assertStatus(200);
        $response->assertSee('masked for privacy');
        $response->assertSee($complaint->masked_complainant);
        $response->assertDontSee('Rodrigo Mendoza');
    }

    public function test_rejected_complaint_shows_red_termination_timeline(): void
    {
        $resident = User::firstOrCreate(
            ['email' => 'resident_rej@example.local'],
            [
                'role_id' => User::ROLE_COMPLAINANT,
                'first_name' => 'Anita',
                'last_name' => 'Batumbakal',
                'password_hash' => bcrypt('secret'),
                'is_active' => true,
            ]
        );

        $complaint = Complaint::create([
            'reference_no' => 'BRGY-2026-55555',
            'complainant_id' => $resident->user_id,
            'report_type_id' => 1,
            'category_id' => 1,
            'priority_id' => 1,
            'area_id' => 1,
            'status_id' => 7, // Rejected
            'title' => 'False report test',
            'description' => 'Test complaint rejected.',
        ]);

        \App\Models\StatusHistory::create([
            'complaint_id' => $complaint->complaint_id,
            'old_status_id' => 1,
            'new_status_id' => 7,
            'changed_by' => $this->admin->user_id,
            'remarks' => 'Does not fall under barangay jurisdiction',
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/complaints/{$complaint->reference_no}");
        $response->assertStatus(200);
        $response->assertSee('Report Rejected / Declined');
        $response->assertSee('Does not fall under barangay jurisdiction');
        $response->assertSee('step rejected', false);
    }

    public function test_admin_reports_displays_purok_area_distribution(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/reports');
        $response->assertStatus(200);
        $response->assertSee('Complaints by Purok / Area');
        $response->assertSee('areaChart');
    }

    public function test_admin_can_manage_committee_rosters_and_category_departments(): void
    {
        $infraDept = \App\Models\Department::firstOrCreate(
            ['department_name' => 'Infrastructure Committee'],
            ['description' => 'Handles roads, drainage, and public facilities', 'is_active' => true]
        );

        $officer = User::create([
            'role_id' => User::ROLE_ADMIN,
            'first_name' => 'Engr. Ramon',
            'last_name' => 'Magsaysay',
            'email' => 'ramon.infra@barangay.local',
            'password_hash' => bcrypt('password123'),
            'department_id' => $infraDept->department_id,
            'committee_position' => 'Committee Chairman',
            'is_active' => true,
        ]);

        // Check departments index displays roster
        $deptResp = $this->actingAs($this->admin)->get('/admin/departments');
        $deptResp->assertStatus(200);
        $deptResp->assertSee('Infrastructure Committee');
        $deptResp->assertSee('Engr. Ramon Magsaysay');
        $deptResp->assertSee('Committee Chairman');

        // Create category mapped to Infrastructure Committee
        $categoryResp = $this->actingAs($this->admin)->post('/admin/categories', [
            'category_name' => 'Bridge & Drainage Damage',
            'department_id' => $infraDept->department_id,
            'description' => 'Structural bridge issues and clogged culverts',
        ]);
        $categoryResp->assertSessionHas('success');

        $cat = \App\Models\ComplaintCategory::where('category_name', 'Bridge & Drainage Damage')->first();
        $this->assertNotNull($cat);
        $this->assertEquals($infraDept->department_id, $cat->department_id);

        // Verify categories index lists the assigned committee
        $catListResp = $this->actingAs($this->admin)->get('/admin/categories');
        $catListResp->assertStatus(200);
        $catListResp->assertSee('Bridge & Drainage Damage');
        $catListResp->assertSee('Infrastructure Committee');
    }

    public function test_infrastructure_complaint_recommends_committee_and_allows_officer_assignment(): void
    {
        $infraDept = \App\Models\Department::firstOrCreate(
            ['department_name' => 'Infrastructure Committee'],
            ['description' => 'Handles roads and public works', 'is_active' => true]
        );

        $infraOfficer = User::create([
            'role_id' => User::ROLE_ADMIN,
            'first_name' => 'Kagawad Juan',
            'last_name' => 'Bautista',
            'email' => 'juan.infra@barangay.local',
            'password_hash' => bcrypt('password123'),
            'department_id' => $infraDept->department_id,
            'committee_position' => 'Committee Head',
            'is_active' => true,
        ]);

        $category = \App\Models\ComplaintCategory::firstOrCreate(
            ['category_name' => 'Road / Infrastructure'],
            ['department_id' => $infraDept->department_id, 'is_active' => true]
        );
        $category->update(['department_id' => $infraDept->department_id]);

        $resident = User::firstOrCreate(
            ['email' => 'resident_infra@example.local'],
            [
                'role_id' => User::ROLE_COMPLAINANT,
                'first_name' => 'Pedro',
                'last_name' => 'Penduko',
                'password_hash' => bcrypt('secret'),
                'is_active' => true,
            ]
        );

        $complaint = Complaint::create([
            'reference_no' => 'BRGY-2026-44444',
            'complainant_id' => $resident->user_id,
            'report_type_id' => 1,
            'category_id' => $category->category_id,
            'priority_id' => 3, // High
            'area_id' => 1,
            'status_id' => 1, // Pending
            'title' => 'Deep pothole on Main Barangay Road',
            'description' => 'Hazardous pothole causing motor vehicular accidents.',
        ]);

        // Detail view should display recommended committee banner and officer quick pick
        $detailResp = $this->actingAs($this->admin)->get("/admin/complaints/{$complaint->reference_no}");
        $detailResp->assertStatus(200);
        $detailResp->assertSee('Recommended Committee');
        $detailResp->assertSee('Infrastructure Committee');
        $detailResp->assertSee('Kagawad Juan Bautista');
        $detailResp->assertSee('Committee Head');

        // Admin assigns officer from Infrastructure Committee
        $assignResp = $this->actingAs($this->admin)->post("/admin/complaints/{$complaint->reference_no}/assign", [
            'assigned_to' => $infraOfficer->user_id,
            'remarks' => 'Conduct immediate road inspection and patch with asphalt mixture.',
        ]);
        $assignResp->assertSessionHas('success');

        $complaint->refresh();
        $this->assertEquals(3, $complaint->status_id); // Auto-moved to Assigned
        $this->assertNotNull($complaint->currentAssignment);
        $this->assertEquals($infraOfficer->user_id, $complaint->currentAssignment->assigned_to);
        $this->assertStringContainsString('asphalt mixture', $complaint->currentAssignment->remarks);

        // Verify notification to officer
        $this->assertDatabaseHas('notifications', [
            'user_id' => $infraOfficer->user_id,
            'complaint_id' => $complaint->complaint_id,
        ]);

        // Verify audit log has officer and committee details
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'ASSIGN',
            'record_id' => $complaint->complaint_id,
        ]);
    }

    public function test_user_show_route_redirects_to_edit(): void
    {
        $resident = User::factory()->create();
        $response = $this->actingAs($this->admin)->get("/admin/users/{$resident->user_id}");
        $response->assertRedirect(route('admin.users.edit', $resident));
    }

    public function test_user_with_activity_is_deactivated_with_warning_on_delete(): void
    {
        $officer = User::factory()->create([
            'role_id' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);
        $resident = User::factory()->create();

        $complaint = Complaint::create([
            'reference_no' => 'BRGY-2026-99991',
            'complainant_id' => $resident->user_id,
            'report_type_id' => 1,
            'category_id' => 1,
            'priority_id' => 1,
            'area_id' => 1,
            'status_id' => 1,
            'title' => 'Test complaint title',
            'description' => 'Test description at least 20 chars long',
        ]);

        \App\Models\ComplaintAssignment::create([
            'complaint_id' => $complaint->complaint_id,
            'assigned_to' => $officer->user_id,
            'assigned_by' => $this->admin->user_id,
            'is_current' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->from(route('admin.users.index'))
            ->delete("/admin/users/{$officer->user_id}");
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('warning');

        $officer->refresh();
        $this->assertFalse($officer->is_active);
        $this->assertDatabaseHas('users', ['user_id' => $officer->user_id]);
    }

    public function test_user_factory_generates_valid_user(): void
    {
        $user = User::factory()->create();
        $this->assertNotNull($user->user_id);
        $this->assertDatabaseHas('users', ['user_id' => $user->user_id]);
    }

    public function test_area_with_complaints_is_deactivated_with_warning(): void
    {
        $area = Area::first();
        User::factory()->create(['area_id' => $area->area_id]);

        $response = $this->actingAs($this->admin)->delete("/admin/areas/{$area->area_id}");
        $response->assertSessionHas('warning');

        $area->refresh();
        $this->assertFalse($area->is_active);
        $this->assertDatabaseHas('areas', ['area_id' => $area->area_id]);
    }
}
