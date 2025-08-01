<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('exam_steps', function (Blueprint $table) {
      $table->id();
      $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
      $table->foreignId('test_id')->constrained('tests')->cascadeOnDelete();
      $table->unsignedInteger('step_order'); // 1, 2, 3, ...
      $table->unsignedInteger('duration')->default(0); // default minutes for this step
      $table->timestamps();
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('exam_steps');
  }
};
