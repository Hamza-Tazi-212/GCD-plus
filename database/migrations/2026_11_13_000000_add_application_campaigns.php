<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('research_subjects', function (Blueprint $table) {
            $table->string('campaign')->default('2026–2027');
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->string('campaign')->default('2026–2027');
            $table->index(['candidate_id', 'campaign']);
        });

        DB::table('applications')->update(['campaign' => '2026–2027']);
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropIndex(['candidate_id', 'campaign']);
            $table->dropColumn('campaign');
        });
        Schema::table('research_subjects', fn (Blueprint $table) => $table->dropColumn('campaign'));
    }
};
