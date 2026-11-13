<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('function');
            $table->string('revision', 64);
            $table->boolean('approved');
            $table->text('comment');
            $table->timestamps();
            $table->unique(['research_subject_id', 'function', 'revision']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_approvals');
    }
};
