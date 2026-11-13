<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctoral_enrolments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained()->restrictOnDelete();
            $table->string('academic_year');
            $table->foreignId('confirmed_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('renewals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctoral_enrolment_id')->constrained()->restrictOnDelete();
            $table->string('academic_year');
            $table->text('report');
            $table->boolean('extension')->default(false);
            $table->string('status')->default('submitted');
            $table->text('supervisor_comment')->nullable();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('supervisor_reviewed_at')->nullable();
            $table->text('administrative_comment')->nullable();
            $table->foreignId('administrator_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
            $table->unique(['doctoral_enrolment_id', 'academic_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('renewals');
        Schema::dropIfExists('doctoral_enrolments');
    }
};
