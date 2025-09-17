<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('paused_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_step_status_id')->constrained('exam_step_statuses')->cascadeOnDelete();
            $table->foreignId('assignment_id')->nullable()->constrained('test_assignments')->nullOnDelete();
            $table->json('progress_json');
            $table->timestamps();

            $table->unique('exam_step_status_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paused_tests');
    }
};
