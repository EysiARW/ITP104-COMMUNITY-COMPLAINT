<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('verification_doc_path')->nullable()->after('address');
            $table->string('verification_doc_type', 60)->nullable()->after('verification_doc_path');
            $table->string('verification_doc_name')->nullable()->after('verification_doc_type');
            $table->string('verification_status', 20)->default('approved')->after('verification_doc_name');
            $table->string('rejection_reason')->nullable()->after('verification_status');
            $table->dateTime('verified_at')->nullable()->after('rejection_reason');
            $table->unsignedInteger('verified_by')->nullable()->after('verified_at');

            $table->foreign('verified_by')->references('user_id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'verification_doc_path',
                'verification_doc_type',
                'verification_doc_name',
                'verification_status',
                'rejection_reason',
                'verified_at',
                'verified_by',
            ]);
        });
    }
};
