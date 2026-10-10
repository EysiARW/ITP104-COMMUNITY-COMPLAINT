<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\PriorityLevel;
use App\Models\ReportType;
use App\Models\Role;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ComplainantModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_public_pages_are_accessible(): void
    {
        $this->get('/')->assertStatus(200)->assertSee('Barangay Banlic');
        $this->get('/track')->assertStatus(200)->assertSee('Track a Report');
        $this->get('/privacy')->assertStatus(200);
        $this->get('/login')->assertStatus(200)->assertSee('Welcome back');
        $this->get('/register')->assertStatus(200)->assertSee('Create your account');
    }

    public function test_registration_validation_rules(): void
    {
        // Submitting empty form should fail
        $response = $this->post('/register', []);
        $response->assertSessionHasErrors([
            'first_name', 'last_name', 'email', 'contact_number', 'area_id', 'address',
            'password', 'verification_doc_type', 'verification_doc', 'privacy'
        ]);

        // Submitting with missing document should fail
        $response2 = $this->post('/register', [
            'first_name' => 'Juan',
            'last_name' => 'Cruz',
            'email' => 'juan@example.local',
            'contact_number' => '09171234567',
            'area_id' => 1,
            'address' => 'Purok 2',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'verification_doc_type' => 'voters_cert',
            'privacy' => 1,
        ]);
        $response2->assertSessionHasErrors('verification_doc');
    }

    public function test_registration_flow_and_login_blocking(): void
    {
        Storage::fake('local');
        $fakeDoc = UploadedFile::fake()->create('valid_id.jpg', 500, 'image/jpeg');

        $regResponse = $this->post('/register', [
            'first_name' => 'Kiko',
            'last_name' => 'Matsing',
            'email' => 'kiko@example.local',
            'contact_number' => '09181234567',
            'area_id' => 1,
            'address' => 'Purok 1, Banlic',
            'password' => 'Secr3tP@ssword!',
            'password_confirmation' => 'Secr3tP@ssword!',
            'verification_doc_type' => 'valid_id',
            'verification_doc' => $fakeDoc,
            'privacy' => 1,
        ]);

        $regResponse->assertRedirect(route('login'));
        $regResponse->assertSessionHas('success');

        $user = User::where('email', 'kiko@example.local')->first();
        $this->assertNotNull($user);
        $this->assertEquals(User::STATUS_PENDING, $user->verification_status);
        $this->assertNotNull($user->verification_doc_path);

        // Attempting to log in as pending user should be blocked
        $loginAttempt = $this->post('/login', [
            'email' => 'kiko@example.local',
            'password' => 'Secr3tP@ssword!',
        ]);
        $loginAttempt->assertSessionHasErrors('email');
        $this->assertGuest();

        // Admin rejects the applicant
        $user->update([
            'verification_status' => User::STATUS_REJECTED,
            'rejection_reason' => 'Blurred ID photo, please re-submit clear copy.',
        ]);

        // Attempting login as rejected user should display rejection reason
        $loginRejected = $this->post('/login', [
            'email' => 'kiko@example.local',
            'password' => 'Secr3tP@ssword!',
        ]);
        $loginRejected->assertSessionHasErrors('email');
        $this->assertGuest();

        // Admin approves applicant
        $user->update([
            'verification_status' => User::STATUS_APPROVED,
            'rejection_reason' => null,
            'is_active' => true,
        ]);

        // Now login succeeds
        $loginApproved = $this->post('/login', [
            'email' => 'kiko@example.local',
            'password' => 'Secr3tP@ssword!',
        ]);
        $loginApproved->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_resident_can_file_and_view_complaint(): void
    {
        $resident = User::create([
            'role_id' => User::ROLE_COMPLAINANT,
            'first_name' => 'Maria',
            'last_name' => 'Clara',
            'email' => 'maria@example.local',
            'password_hash' => Hash::make('Secret123!'),
            'verification_status' => User::STATUS_APPROVED,
            'is_active' => true,
        ]);

        // Access complaint create page
        $createPage = $this->actingAs($resident)->get('/complaints/create');
        $createPage->assertStatus(200);
        $createPage->assertSee('File a Complaint');
        $createPage->assertSee('data-dtp', false);
        $createPage->assertSee('⚡ Just now');

        // Submit complaint
        $postData = [
            'report_type_id' => 1,
            'category_id' => 1,
            'area_id' => 1,
            'priority_id' => 2,
            'title' => 'Flooding at Purok 1 entrance',
            'description' => 'Drainage is completely blocked by solid trash causing flooding on the street.',
            'incident_date' => now()->format('Y-m-d\TH:i'),
        ];

        $submitResp = $this->actingAs($resident)->post('/complaints', $postData);
        $submitResp->assertSessionHas('success');

        $complaint = Complaint::where('title', 'Flooding at Purok 1 entrance')->first();
        $this->assertNotNull($complaint);
        $this->assertEquals($resident->user_id, $complaint->complainant_id);
        $this->assertStringStartsWith('BRGY-' . date('Y') . '-', $complaint->reference_no);

        // Resident views complaint details
        $detailResp = $this->actingAs($resident)->get("/complaints/{$complaint->reference_no}");
        $detailResp->assertStatus(200);
        $detailResp->assertSee($complaint->reference_no);
        $detailResp->assertSee('Flooding at Purok 1 entrance');
        $detailResp->assertSee('Pending');

        // Resident views resident dashboard
        $dashResp = $this->actingAs($resident)->get('/dashboard');
        $dashResp->assertStatus(200);
        $dashResp->assertSee($complaint->reference_no);
    }

    public function test_resident_profile_and_password_update(): void
    {
        $resident = User::create([
            'role_id' => User::ROLE_COMPLAINANT,
            'first_name' => 'Liza',
            'last_name' => 'Soberano',
            'email' => 'liza@example.local',
            'contact_number' => '09171112222',
            'password_hash' => Hash::make('OldPassword123!'),
            'verification_status' => User::STATUS_APPROVED,
            'is_active' => true,
        ]);

        // Edit profile
        $editResp = $this->actingAs($resident)->get('/profile');
        $editResp->assertStatus(200);

        // Update profile details
        $updateResp = $this->actingAs($resident)->put('/profile', [
            'first_name' => 'Elizabeth',
            'last_name' => 'Soberano',
            'email' => 'liza@example.local',
            'contact_number' => '09191234567',
            'address' => 'Purok 3, Banlic',
        ]);
        $updateResp->assertSessionHas('success');

        $resident->refresh();
        $this->assertEquals('Elizabeth', $resident->first_name);
        $this->assertEquals('09191234567', $resident->contact_number);

        // Update password
        $pwdResp = $this->actingAs($resident)->put('/profile/password', [
            'current_password' => 'OldPassword123!',
            'password' => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ]);
        $pwdResp->assertSessionHas('success');

        $resident->refresh();
        $this->assertTrue(Hash::check('NewPassword456!', $resident->password_hash));
    }

    public function test_resident_notifications_and_read_all(): void
    {
        $resident = User::create([
            'role_id' => User::ROLE_COMPLAINANT,
            'first_name' => 'Alden',
            'last_name' => 'Richards',
            'email' => 'alden@example.local',
            'password_hash' => Hash::make('Secret123!'),
            'verification_status' => User::STATUS_APPROVED,
            'is_active' => true,
        ]);

        $notification = UserNotification::create([
            'user_id' => $resident->user_id,
            'message' => 'Your complaint status has been updated.',
            'is_read' => false,
        ]);

        $this->assertFalse($notification->is_read);

        $readResp = $this->actingAs($resident)->post('/notifications/read-all');
        $readResp->assertStatus(302);

        $notification->refresh();
        $this->assertTrue($notification->is_read);
    }

    public function test_attachment_serving_when_file_exists(): void
    {
        Storage::fake('local');
        $resident = User::create([
            'role_id' => User::ROLE_COMPLAINANT,
            'first_name' => 'Sara',
            'last_name' => 'Duterte',
            'email' => 'sara@example.local',
            'password_hash' => Hash::make('Secret123!'),
            'verification_status' => User::STATUS_APPROVED,
            'is_active' => true,
        ]);

        $complaint = Complaint::create([
            'reference_no' => 'BRGY-2026-99001',
            'complainant_id' => $resident->user_id,
            'report_type_id' => 1,
            'category_id' => 1,
            'priority_id' => 2,
            'area_id' => 1,
            'status_id' => 1,
            'title' => 'Evidence test',
            'description' => 'Test complaint with uploaded evidence file.',
        ]);

        $file = UploadedFile::fake()->create('proof.png', 120, 'image/png');
        $path = $file->store('complaints/' . $complaint->complaint_id, 'local');

        $attachment = \App\Models\Attachment::create([
            'complaint_id' => $complaint->complaint_id,
            'uploaded_by' => $resident->user_id,
            'file_name' => 'proof.png',
            'file_type' => 'image/png',
            'file_size' => 120,
            'file_path' => $path,
        ]);

        $response = $this->actingAs($resident)->get("/attachments/{$attachment->attachment_id}");
        $response->assertStatus(200);
        $this->assertStringContainsString('inline', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_attachment_renders_helpful_view_when_file_missing_on_server(): void
    {
        $resident = User::create([
            'role_id' => User::ROLE_COMPLAINANT,
            'first_name' => 'Isko',
            'last_name' => 'Moreno',
            'email' => 'isko@example.local',
            'password_hash' => Hash::make('Secret123!'),
            'verification_status' => User::STATUS_APPROVED,
            'is_active' => true,
        ]);

        $complaint = Complaint::create([
            'reference_no' => 'BRGY-2026-99002',
            'complainant_id' => $resident->user_id,
            'report_type_id' => 1,
            'category_id' => 1,
            'priority_id' => 2,
            'area_id' => 1,
            'status_id' => 1,
            'title' => 'Missing evidence test',
            'description' => 'Test complaint where evidence was wiped after container restart.',
        ]);

        $attachment = \App\Models\Attachment::create([
            'complaint_id' => $complaint->complaint_id,
            'uploaded_by' => $resident->user_id,
            'file_name' => 'erased_photo.png',
            'file_type' => 'image/png',
            'file_size' => 204800,
            'file_path' => 'complaints/99002/non_existent_file.png',
        ]);

        $response = $this->actingAs($resident)->get("/attachments/{$attachment->attachment_id}");
        $response->assertStatus(404);
        $response->assertSee('Evidence File Not Found on Storage Disk');
        $response->assertSee('erased_photo.png');
        $response->assertSee('Railway');
        $response->assertSee('BRGY-2026-99002');
    }

    public function test_attachment_returns_svg_placeholder_for_image_assets_when_missing(): void
    {
        $resident = User::create([
            'role_id' => User::ROLE_COMPLAINANT,
            'first_name' => 'Vico',
            'last_name' => 'Sotto',
            'email' => 'vico@example.local',
            'password_hash' => Hash::make('Secret123!'),
            'verification_status' => User::STATUS_APPROVED,
            'is_active' => true,
        ]);

        $complaint = Complaint::create([
            'reference_no' => 'BRGY-2026-99003',
            'complainant_id' => $resident->user_id,
            'report_type_id' => 1,
            'category_id' => 1,
            'priority_id' => 2,
            'area_id' => 1,
            'status_id' => 1,
            'title' => 'Missing image thumb test',
            'description' => 'Test complaint where image thumb is requested.',
        ]);

        $attachment = \App\Models\Attachment::create([
            'complaint_id' => $complaint->complaint_id,
            'uploaded_by' => $resident->user_id,
            'file_name' => 'damaged_gate.jpg',
            'file_type' => 'image/jpeg',
            'file_size' => 50000,
            'file_path' => 'complaints/99003/not_on_disk.jpg',
        ]);

        $response = $this->actingAs($resident)->withHeaders([
            'Sec-Fetch-Dest' => 'image',
            'Accept' => 'image/webp,image/png,image/*;q=0.8',
        ])->get("/attachments/{$attachment->attachment_id}");

        $response->assertStatus(200);
        $this->assertEquals('image/svg+xml', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('File unavailable on server', $response->getContent());
    }

    public function test_unauthorized_user_cannot_access_other_users_evidence(): void
    {
        $owner = User::create([
            'role_id' => User::ROLE_COMPLAINANT,
            'first_name' => 'Manny',
            'last_name' => 'Pacquiao',
            'email' => 'manny@example.local',
            'password_hash' => Hash::make('Secret123!'),
            'verification_status' => User::STATUS_APPROVED,
            'is_active' => true,
        ]);

        $stranger = User::create([
            'role_id' => User::ROLE_COMPLAINANT,
            'first_name' => 'Juan',
            'last_name' => 'Marquez',
            'email' => 'marquez@example.local',
            'password_hash' => Hash::make('Secret123!'),
            'verification_status' => User::STATUS_APPROVED,
            'is_active' => true,
        ]);

        $complaint = Complaint::create([
            'reference_no' => 'BRGY-2026-99004',
            'complainant_id' => $owner->user_id,
            'report_type_id' => 1,
            'category_id' => 1,
            'priority_id' => 2,
            'area_id' => 1,
            'status_id' => 1,
            'title' => 'Private evidence test',
            'description' => 'Owner only complaint.',
        ]);

        $attachment = \App\Models\Attachment::create([
            'complaint_id' => $complaint->complaint_id,
            'uploaded_by' => $owner->user_id,
            'file_name' => 'private_cctv.png',
            'file_type' => 'image/png',
            'file_size' => 1000,
            'file_path' => 'complaints/99004/cctv.png',
        ]);

        $response = $this->actingAs($stranger)->get("/attachments/{$attachment->attachment_id}");
        $response->assertStatus(403);
    }

    public function test_registration_fails_with_inactive_area(): void
    {
        $inactiveArea = Area::create([
            'area_name' => 'Inactive Sub-purok',
            'is_active' => false,
        ]);

        $response = $this->post('/register', [
            'first_name' => 'Juan',
            'last_name' => 'Luna',
            'email' => 'juanluna@example.local',
            'contact_number' => '09171234567',
            'area_id' => $inactiveArea->area_id,
            'address' => 'Purok X',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'verification_doc_type' => 'voters_cert',
            'privacy' => 1,
        ]);

        $response->assertSessionHasErrors('area_id');
    }

    public function test_complaint_filing_fails_with_inactive_category_or_area(): void
    {
        $resident = User::firstOrCreate(
            ['email' => 'active_filer@example.local'],
            [
                'role_id' => User::ROLE_COMPLAINANT,
                'first_name' => 'Active',
                'last_name' => 'Filer',
                'password_hash' => bcrypt('secret'),
                'is_active' => true,
            ]
        );

        $inactiveCat = \App\Models\ComplaintCategory::create([
            'category_name' => 'Inactive Category',
            'is_active' => false,
        ]);

        $inactiveArea = Area::create([
            'area_name' => 'Old Inactive Purok',
            'is_active' => false,
        ]);

        $respCat = $this->actingAs($resident)->post('/complaints', [
            'report_type_id' => 1,
            'category_id' => $inactiveCat->category_id,
            'area_id' => 1,
            'title' => 'Test Inactive Category Rejection',
            'description' => 'A valid description of what happened in the neighborhood.',
        ]);
        $respCat->assertSessionHasErrors('category_id');

        $respArea = $this->actingAs($resident)->post('/complaints', [
            'report_type_id' => 1,
            'category_id' => 1,
            'area_id' => $inactiveArea->area_id,
            'title' => 'Test Inactive Area Rejection',
            'description' => 'A valid description of what happened in the neighborhood.',
        ]);
        $respArea->assertSessionHasErrors('area_id');
    }

    public function test_status_history_and_assignment_timestamps_auto_populated(): void
    {
        $resident = User::firstOrCreate(
            ['email' => 'timestamp_tester@example.local'],
            [
                'role_id' => User::ROLE_COMPLAINANT,
                'first_name' => 'Timer',
                'last_name' => 'Tester',
                'password_hash' => bcrypt('secret'),
                'is_active' => true,
            ]
        );

        $complaint = Complaint::create([
            'reference_no' => 'BRGY-2026-11223',
            'complainant_id' => $resident->user_id,
            'report_type_id' => 1,
            'category_id' => 1,
            'priority_id' => 2,
            'area_id' => 1,
            'status_id' => 1,
            'title' => 'Timestamp Test',
            'description' => 'Checking in-memory auto-population of timestamps.',
        ]);

        $history = \App\Models\StatusHistory::create([
            'complaint_id' => $complaint->complaint_id,
            'old_status_id' => null,
            'new_status_id' => 1,
            'changed_by' => $resident->user_id,
        ]);

        $this->assertNotNull($history->changed_at);

        $assignment = \App\Models\ComplaintAssignment::create([
            'complaint_id' => $complaint->complaint_id,
            'assigned_to' => $resident->user_id,
            'assigned_by' => $resident->user_id,
            'is_current' => true,
        ]);

        $this->assertNotNull($assignment->assigned_at);
    }
}

