<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('test_result_manual_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_result_id')->constrained('test_results')->cascadeOnDelete();
            $table->string('key');
            $table->decimal('value', 10, 2)->nullable();
            $table->timestamps();

            $table->unique(['test_result_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_result_manual_scores');
    }
};
