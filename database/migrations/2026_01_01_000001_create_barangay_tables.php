<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Barangay Complaint & Incident Reporting System (3NF).
 * Mirrors barangay_complaint_system.sql.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Replace Laravel's default users table (it uses `id`/`password`; ours uses `user_id`/`password_hash`).
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();

        Schema::create('roles', function (Blueprint $t) {
            $t->tinyIncrements('role_id');
            $t->string('role_name', 30)->unique();
            $t->string('description', 150)->nullable();
        });
        Schema::create('departments', function (Blueprint $t) {
            $t->increments('department_id');
            $t->string('department_name', 100)->unique();
            $t->string('description')->nullable();
            $t->boolean('is_active')->default(true);
        });
        Schema::create('areas', function (Blueprint $t) {
            $t->increments('area_id');
            $t->string('area_name', 100)->unique();
            $t->string('description')->nullable();
            $t->boolean('is_active')->default(true);
        });
        Schema::create('report_types', function (Blueprint $t) {
            $t->tinyIncrements('report_type_id');
            $t->string('type_name', 50)->unique();
        });
        Schema::create('complaint_categories', function (Blueprint $t) {
            $t->increments('category_id');
            $t->string('category_name', 100)->unique();
            $t->string('description')->nullable();
            $t->boolean('is_active')->default(true);
        });
        Schema::create('priority_levels', function (Blueprint $t) {
            $t->tinyIncrements('priority_id');
            $t->string('priority_name', 30)->unique();
            $t->unsignedTinyInteger('sort_order');
        });
        Schema::create('complaint_statuses', function (Blueprint $t) {
            $t->tinyIncrements('status_id');
            $t->string('status_name', 50)->unique();
            $t->unsignedTinyInteger('sort_order');
            $t->string('badge_color', 20)->nullable();
        });

        Schema::create('users', function (Blueprint $t) {
            $t->increments('user_id');
            $t->unsignedTinyInteger('role_id');
            $t->unsignedInteger('department_id')->nullable();
            $t->unsignedInteger('area_id')->nullable();
            $t->string('first_name', 60);
            $t->string('middle_name', 60)->nullable();
            $t->string('last_name', 60);
            $t->string('email', 120)->unique();
            $t->string('password_hash');
            $t->string('contact_number', 20)->nullable();
            $t->string('address')->nullable();
            $t->boolean('is_active')->default(true);
            $t->rememberToken();
            $t->timestamps();
            $t->foreign('role_id')->references('role_id')->on('roles');
            $t->foreign('department_id')->references('department_id')->on('departments')->nullOnDelete();
            $t->foreign('area_id')->references('area_id')->on('areas')->nullOnDelete();
        });

        // Laravel's password broker uses this table (already created by the default migration).
        if (! Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $t) {
                $t->string('email')->primary();
                $t->string('token');
                $t->timestamp('created_at')->nullable();
            });
        }

        Schema::create('area_staff', function (Blueprint $t) {
            $t->unsignedInteger('area_id');
            $t->unsignedInteger('user_id');
            $t->timestamp('assigned_at')->useCurrent();
            $t->primary(['area_id', 'user_id']);
            $t->foreign('area_id')->references('area_id')->on('areas')->cascadeOnDelete();
            $t->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
        });

        Schema::create('complaints', function (Blueprint $t) {
            $t->increments('complaint_id');
            $t->string('reference_no', 30)->unique();
            $t->unsignedInteger('complainant_id');
            $t->unsignedTinyInteger('report_type_id');
            $t->unsignedInteger('category_id');
            $t->unsignedTinyInteger('priority_id');
            $t->unsignedInteger('area_id');
            $t->unsignedTinyInteger('status_id');
            $t->string('title', 150);
            $t->text('description');
            $t->string('location_details')->nullable();
            $t->dateTime('incident_date')->nullable();
            $t->timestamps();
            $t->foreign('complainant_id')->references('user_id')->on('users');
            $t->foreign('report_type_id')->references('report_type_id')->on('report_types');
            $t->foreign('category_id')->references('category_id')->on('complaint_categories');
            $t->foreign('priority_id')->references('priority_id')->on('priority_levels');
            $t->foreign('area_id')->references('area_id')->on('areas');
            $t->foreign('status_id')->references('status_id')->on('complaint_statuses');
            $t->index('status_id');
            $t->index('created_at');
        });

        Schema::create('complaint_assignments', function (Blueprint $t) {
            $t->increments('assignment_id');
            $t->unsignedInteger('complaint_id');
            $t->unsignedInteger('assigned_to');
            $t->unsignedInteger('assigned_by');
            $t->string('remarks')->nullable();
            $t->boolean('is_current')->default(true);
            $t->timestamp('assigned_at')->useCurrent();
            $t->foreign('complaint_id')->references('complaint_id')->on('complaints')->cascadeOnDelete();
            $t->foreign('assigned_to')->references('user_id')->on('users');
            $t->foreign('assigned_by')->references('user_id')->on('users');
            $t->index(['assigned_to', 'is_current']);
        });

        Schema::create('complaint_status_history', function (Blueprint $t) {
            $t->increments('history_id');
            $t->unsignedInteger('complaint_id');
            $t->unsignedTinyInteger('old_status_id')->nullable();
            $t->unsignedTinyInteger('new_status_id');
            $t->unsignedInteger('changed_by');
            $t->string('remarks')->nullable();
            $t->timestamp('changed_at')->useCurrent();
            $t->foreign('complaint_id')->references('complaint_id')->on('complaints')->cascadeOnDelete();
            $t->foreign('old_status_id')->references('status_id')->on('complaint_statuses');
            $t->foreign('new_status_id')->references('status_id')->on('complaint_statuses');
            $t->foreign('changed_by')->references('user_id')->on('users');
            $t->index(['complaint_id', 'changed_at']);
        });

        Schema::create('investigations', function (Blueprint $t) {
            $t->increments('investigation_id');
            $t->unsignedInteger('complaint_id');
            $t->unsignedInteger('investigator_id');
            $t->dateTime('investigation_date');
            $t->text('findings');
            $t->text('actions_taken')->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->foreign('complaint_id')->references('complaint_id')->on('complaints')->cascadeOnDelete();
            $t->foreign('investigator_id')->references('user_id')->on('users');
        });

        Schema::create('resolutions', function (Blueprint $t) {
            $t->increments('resolution_id');
            $t->unsignedInteger('complaint_id')->unique();
            $t->unsignedInteger('resolved_by');
            $t->text('resolution_details');
            $t->dateTime('resolved_at');
            $t->timestamp('created_at')->useCurrent();
            $t->foreign('complaint_id')->references('complaint_id')->on('complaints')->cascadeOnDelete();
            $t->foreign('resolved_by')->references('user_id')->on('users');
        });

        Schema::create('attachments', function (Blueprint $t) {
            $t->increments('attachment_id');
            $t->unsignedInteger('complaint_id');
            $t->unsignedInteger('investigation_id')->nullable();
            $t->unsignedInteger('uploaded_by');
            $t->string('file_name');
            $t->string('file_path');
            $t->string('file_type', 100)->nullable();
            $t->unsignedInteger('file_size')->nullable();
            $t->timestamp('uploaded_at')->useCurrent();
            $t->foreign('complaint_id')->references('complaint_id')->on('complaints')->cascadeOnDelete();
            $t->foreign('investigation_id')->references('investigation_id')->on('investigations')->nullOnDelete();
            $t->foreign('uploaded_by')->references('user_id')->on('users');
        });

        Schema::create('notifications', function (Blueprint $t) {
            $t->increments('notification_id');
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('complaint_id')->nullable();
            $t->string('message');
            $t->boolean('is_read')->default(false);
            $t->timestamp('created_at')->useCurrent();
            $t->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
            $t->foreign('complaint_id')->references('complaint_id')->on('complaints')->cascadeOnDelete();
            $t->index(['user_id', 'is_read']);
        });

        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id('log_id');
            $t->unsignedInteger('user_id')->nullable();
            $t->string('action', 50);
            $t->string('table_name', 60)->nullable();
            $t->unsignedInteger('record_id')->nullable();
            $t->string('description')->nullable();
            $t->string('ip_address', 45)->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->foreign('user_id')->references('user_id')->on('users')->nullOnDelete();
            $t->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach (['audit_logs','notifications','attachments','resolutions','investigations','complaint_status_history',
                  'complaint_assignments','complaints','area_staff','users','complaint_statuses','priority_levels',
                  'complaint_categories','report_types','areas','departments','roles'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::enableForeignKeyConstraints();
    }
};
