<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('exam_step_statuses', function (Blueprint $table) {
      $table->id();
      $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
      $table->foreignId('participant_id')->constrained('users')->cascadeOnDelete();
      $table->foreignId('exam_step_id')->constrained('exam_steps')->cascadeOnDelete();
      $table->enum('status', ['not_started', 'in_progress', 'waiting', 'completed'])->default('not_started');
      $table->integer('grace_period_seconds')->default(0)
      $table->unsignedInteger('duration')->nullable(); // actual duration (in minutes)
      $table->unsignedInteger('extra_time')->default(0); // minutes of extra time
      $table->timestamp('started_at')->nullable();
      $table->timestamp('completed_at')->nullable();
      $table->timestamps();
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('exam_step_statuses');
  }
};
