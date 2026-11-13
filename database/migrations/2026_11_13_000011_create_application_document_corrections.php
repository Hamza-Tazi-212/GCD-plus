<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_document_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['application_document_id', 'resolved_at'], 'document_open_corrections');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_document_corrections');
    }
};
