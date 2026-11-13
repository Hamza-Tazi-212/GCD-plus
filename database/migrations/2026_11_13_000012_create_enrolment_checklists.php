<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrolment_checklists', function (Blueprint $table) {
            $table->id();
            $table->string('campaign')->unique();
            $table->json('kinds');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrolment_checklists');
    }
};
