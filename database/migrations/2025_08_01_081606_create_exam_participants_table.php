<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('exam_participants', function (Blueprint $table) {
      $table->id();
      $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
      $table->foreignId('participant_id')->constrained('users')->cascadeOnDelete();
      $table->enum('status', ['not_started', 'in_progress', 'waiting', 'completed'])->default('not_started');
      $table->timestamps();
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('exam_participants');
  }
};
