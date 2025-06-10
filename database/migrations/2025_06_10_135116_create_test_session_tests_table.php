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
        Schema::create('test_session_tests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('test_session_id');
            $table->foreign('test_session_id')->references('id')->on('test_sessions')->onDelete('cascade');
            $table->unsignedBigInteger('test_id');
            $table->foreign('test_id')->references('id')->on('tests')->onDelete('cascade');
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->integer('timer_override')->nullable()->comment('seconds');
            $table->enum('status', ['pending', 'active', 'paused', 'completed', 'skipped'])->default('pending');
            $table->decimal('score')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_session_tests');
    }
};
