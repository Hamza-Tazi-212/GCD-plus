<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('renewal_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('renewal_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('function');
            $table->boolean('approved');
            $table->text('comment');
            $table->timestamps();
            $table->unique(['renewal_id', 'function']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('renewal_approvals');
    }
};
