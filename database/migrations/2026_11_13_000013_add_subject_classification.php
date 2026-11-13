<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('research_subjects', function (Blueprint $table) {
            $table->string('laboratory', 120)->nullable();
            $table->string('field', 120)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('research_subjects', fn (Blueprint $table) => $table->dropColumn(['laboratory', 'field']));
    }
};
