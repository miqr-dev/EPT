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
        Schema::create('test_battery_tests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('test_battery_id');
            $table->foreign('test_battery_id')->references('id')->on('test_batteries')->onDelete('cascade');
            $table->unsignedBigInteger('test_id');
            $table->foreign('test_id')->references('id')->on('tests')->onDelete('cascade');
            $table->integer('order');
            // No timestamps for this pivot table
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_battery_tests');
    }
};
