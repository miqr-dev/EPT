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
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('klasse')->nullable();
            $table->integer('age_year')->nullable();
            $table->integer('age_month')->nullable();
            $table->string('german_level')->nullable();
            $table->string('english_level')->nullable();
            $table->string('math_level')->nullable();
            $table->string('math_note')->nullable();
            $table->string('schulzweig')->nullable();
            $table->string('schule')->nullable();
            $table->string('schulart')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
