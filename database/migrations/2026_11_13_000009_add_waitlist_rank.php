<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->unsignedInteger('waitlist_rank')->nullable();
            $table->unique(['subject_id', 'campaign', 'waitlist_rank'], 'applications_waitlist_rank_unique');
        });

        Schema::table('application_status_logs', function (Blueprint $table) {
            $table->unsignedInteger('waitlist_rank')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('application_status_logs', fn (Blueprint $table) => $table->dropColumn('waitlist_rank'));
        Schema::table('applications', fn (Blueprint $table) => $table->dropUnique('applications_waitlist_rank_unique'));
        Schema::table('applications', fn (Blueprint $table) => $table->dropColumn('waitlist_rank'));
    }
};
