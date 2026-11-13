<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('campaign_periods', function (Blueprint $table) {
            $table->id();
            $table->string('academic_year');
            $table->string('phase');
            $table->date('opens_on');
            $table->date('closes_on');
            $table->unique(['academic_year', 'phase']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_periods');
    }
};
