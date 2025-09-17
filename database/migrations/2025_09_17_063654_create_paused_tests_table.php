<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('paused_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_step_status_id')->constrained('exam_step_statuses')->cascadeOnDelete();
            $table->json('state_json');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paused_tests');
    }
};
