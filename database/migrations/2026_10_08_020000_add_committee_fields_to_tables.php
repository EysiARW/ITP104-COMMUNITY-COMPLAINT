<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add committee_position to users table
        if (!Schema::hasColumn('users', 'committee_position')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('committee_position', 100)->nullable()->after('department_id');
            });
        }

        // 2. Add department_id to complaint_categories table
        if (!Schema::hasColumn('complaint_categories', 'department_id')) {
            Schema::table('complaint_categories', function (Blueprint $table) {
                $table->unsignedInteger('department_id')->nullable()->after('description');
                $table->foreign('department_id')->references('department_id')->on('departments')->nullOnDelete();
            });
        }

        // 3. Map existing categories to existing default departments
        $deptMap = [
            'Road / Infrastructure' => 'Infrastructure Committee',
            'Noise Disturbance' => 'Peace and Order Committee',
            'Peace and Order' => 'Peace and Order Committee',
            'Theft / Property' => 'Peace and Order Committee',
            'Garbage / Sanitation' => 'Health and Sanitation Committee',
            'Health and Safety' => 'Health and Sanitation Committee',
            'Neighbor Dispute' => 'Lupon Tagapamayapa',
            'Domestic / VAWC' => 'Women and Children Desk',
            'Animal Concerns' => 'Health and Sanitation Committee',
        ];

        foreach ($deptMap as $catName => $deptName) {
            $dept = DB::table('departments')->where('department_name', $deptName)->first();
            if ($dept) {
                DB::table('complaint_categories')
                    ->where('category_name', $catName)
                    ->update(['department_id' => $dept->department_id]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('complaint_categories', 'department_id')) {
            Schema::table('complaint_categories', function (Blueprint $table) {
                $table->dropForeign(['department_id']);
                $table->dropColumn('department_id');
            });
        }

        if (Schema::hasColumn('users', 'committee_position')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('committee_position');
            });
        }
    }
};
