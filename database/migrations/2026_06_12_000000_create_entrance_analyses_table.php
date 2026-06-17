<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('entrance_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('instruction_understanding')->nullable();
            $table->text('work_method')->nullable();
            $table->text('work_speed')->nullable();
            $table->text('group_behavior')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrance_analyses');
    }
};
