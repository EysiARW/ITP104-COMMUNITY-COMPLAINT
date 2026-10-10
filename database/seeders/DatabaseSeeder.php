<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([
            ['role_id' => 1, 'role_name' => 'admin', 'description' => 'Barangay staff: review, assign, investigate, resolve, manage system'],
            ['role_id' => 2, 'role_name' => 'complainant', 'description' => 'Community member who files complaints and incident reports'],
        ]);

        DB::table('report_types')->insert([['type_name' => 'Complaint'], ['type_name' => 'Concern'], ['type_name' => 'Incident Report']]);

        DB::table('priority_levels')->insert([
            ['priority_name' => 'Low', 'sort_order' => 1], ['priority_name' => 'Medium', 'sort_order' => 2],
            ['priority_name' => 'High', 'sort_order' => 3], ['priority_name' => 'Urgent', 'sort_order' => 4],
        ]);

        DB::table('complaint_statuses')->insert([
            ['status_name' => 'Pending', 'sort_order' => 1, 'badge_color' => 'yellow'],
            ['status_name' => 'Under Review', 'sort_order' => 2, 'badge_color' => 'blue'],
            ['status_name' => 'Assigned', 'sort_order' => 3, 'badge_color' => 'indigo'],
            ['status_name' => 'In Progress', 'sort_order' => 4, 'badge_color' => 'orange'],
            ['status_name' => 'Resolved', 'sort_order' => 5, 'badge_color' => 'green'],
            ['status_name' => 'Closed', 'sort_order' => 6, 'badge_color' => 'gray'],
            ['status_name' => 'Rejected', 'sort_order' => 7, 'badge_color' => 'red'],
        ]);

        DB::table('complaint_categories')->insert([
            ['category_name' => 'Noise Disturbance', 'description' => 'Loud music, karaoke, construction noise'],
            ['category_name' => 'Garbage / Sanitation', 'description' => 'Improper waste disposal, clogged drainage'],
            ['category_name' => 'Peace and Order', 'description' => 'Fights, disturbances, threats'],
            ['category_name' => 'Theft / Property', 'description' => 'Theft, vandalism, trespassing'],
            ['category_name' => 'Road / Infrastructure', 'description' => 'Potholes, broken streetlights, damaged roads'],
            ['category_name' => 'Neighbor Dispute', 'description' => 'Boundary, noise, or other disputes between neighbors'],
            ['category_name' => 'Domestic / VAWC', 'description' => 'Violence against women and children, family concerns'],
            ['category_name' => 'Health and Safety', 'description' => 'Hazards, outbreaks, unsafe structures'],
            ['category_name' => 'Animal Concerns', 'description' => 'Stray animals, animal nuisance'],
            ['category_name' => 'Others', 'description' => 'Anything not listed'],
        ]);

        DB::table('departments')->insert([
            ['department_name' => 'Barangay Captain Office', 'description' => 'Executive office'],
            ['department_name' => 'Peace and Order Committee', 'description' => 'Handles disputes, security, and incidents'],
            ['department_name' => 'Health and Sanitation Committee', 'description' => 'Handles health, garbage, sanitation'],
            ['department_name' => 'Infrastructure Committee', 'description' => 'Roads, lighting, public works'],
            ['department_name' => 'Lupon Tagapamayapa', 'description' => 'Barangay conciliation / mediation'],
            ['department_name' => 'Women and Children Desk', 'description' => 'VAWC and related concerns'],
        ]);

        DB::table('areas')->insert([['area_name' => 'Purok 1'], ['area_name' => 'Purok 2'], ['area_name' => 'Purok 3'], ['area_name' => 'Purok 4'], ['area_name' => 'Purok 5']]);

        // Default admin. CHANGE THIS PASSWORD after the first login.
        DB::table('users')->insertOrIgnore([
            'role_id' => 1, 'department_id' => 1, 'first_name' => 'System', 'last_name' => 'Administrator',
            'email' => 'admin@barangay.local', 'password_hash' => Hash::make('Admin@12345'),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        if (!app()->environment('testing')) {
            $this->call(DummyDataSeeder::class);
        }
    }
}
