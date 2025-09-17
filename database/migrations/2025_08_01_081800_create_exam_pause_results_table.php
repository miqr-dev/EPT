<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_pause_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_step_id')->constrained('exam_steps')->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained('users')->cascadeOnDelete();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique(['exam_id', 'exam_step_id', 'participant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_pause_results');
    }
};
