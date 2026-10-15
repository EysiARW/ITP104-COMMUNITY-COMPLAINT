<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationBackNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function createResident(): User
    {
        return User::create([
            'role_id' => User::ROLE_COMPLAINANT,
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => 'juan@example.local',
            'password_hash' => bcrypt('Password123!'),
            'contact_number' => '09171234567',
            'address' => 'Purok 1',
            'area_id' => 1,
            'verification_status' => User::STATUS_APPROVED,
            'is_active' => true,
        ]);
    }

    public function test_response_headers_contain_anti_cache_controls_system_wide(): void
    {
        $loginResponse = $this->get('/login');
        $loginResponse->assertStatus(200);
        $this->assertStringContainsString('no-store', (string) $loginResponse->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-cache', (string) $loginResponse->headers->get('Cache-Control'));
        $this->assertStringContainsString('must-revalidate', (string) $loginResponse->headers->get('Cache-Control'));
        $this->assertEquals('no-cache', $loginResponse->headers->get('Pragma'));

        $admin = User::where('role_id', User::ROLE_ADMIN)->first();
        $adminDashboardResponse = $this->actingAs($admin)->get('/admin/dashboard');
        $adminDashboardResponse->assertStatus(200);
        $this->assertStringContainsString('no-store', (string) $adminDashboardResponse->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-cache', (string) $adminDashboardResponse->headers->get('Cache-Control'));

        $resident = $this->createResident();
        $residentDashboardResponse = $this->actingAs($resident)->get('/dashboard');
        $residentDashboardResponse->assertStatus(200);
        $this->assertStringContainsString('no-store', (string) $residentDashboardResponse->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-cache', (string) $residentDashboardResponse->headers->get('Cache-Control'));
    }

    public function test_authenticated_admin_visiting_guest_pages_is_redirected_to_admin_dashboard(): void
    {
        $admin = User::where('role_id', User::ROLE_ADMIN)->first();

        $this->actingAs($admin)->get('/login')->assertRedirect(route('admin.dashboard'));
        $this->actingAs($admin)->get('/register')->assertRedirect(route('admin.dashboard'));
        $this->actingAs($admin)->get('/forgot-password')->assertRedirect(route('admin.dashboard'));
    }

    public function test_authenticated_resident_visiting_guest_pages_is_redirected_to_resident_dashboard(): void
    {
        $resident = $this->createResident();

        $this->actingAs($resident)->get('/login')->assertRedirect(route('dashboard'));
        $this->actingAs($resident)->get('/register')->assertRedirect(route('dashboard'));
        $this->actingAs($resident)->get('/forgot-password')->assertRedirect(route('dashboard'));
    }

    public function test_logout_invalidates_session_and_subsequent_protected_access_redirects_to_login(): void
    {
        $resident = $this->createResident();

        $this->actingAs($resident)->get('/dashboard')->assertStatus(200);

        // Perform official sign out
        $logoutResponse = $this->actingAs($resident)->post('/logout');
        $logoutResponse->assertRedirect(route('home'));

        // Subsequent attempt to load the dashboard without active session must redirect to login
        $this->get('/dashboard')->assertRedirect(route('login'));

        // Guest can now access login page cleanly
        $this->get('/login')->assertStatus(200);
    }
}
