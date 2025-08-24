<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('exams', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->foreignId('city_id')->constrained()->cascadeOnDelete();
      $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
      $table->enum('status', ['not_started', 'in_progress', 'paused', 'completed '])->default('not_started');
      $table->foreignId('current_exam_step_id')->nullable();
      $table->timestamp('started_at')->nullable();
      $table->timestamp('completed_at')->nullable();
      $table->timestamps();
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('exams');
  }
};
