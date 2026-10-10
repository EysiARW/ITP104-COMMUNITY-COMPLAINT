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
}
