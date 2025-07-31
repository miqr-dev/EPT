<?php

// database/migrations/2024_07_23_000003_create_participant_profiles_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('participant_profiles', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
      $table->string('firstname');
      $table->string('name');
      $table->date('birthday');
      $table->unsignedTinyInteger('age')->nullable(); // Can be calculated, but store for fast filtering
      $table->string('sex');
      $table->string('education')->nullable();
      $table->string('profession')->nullable();
      $table->string('marital_status');
      $table->string('household');
      $table->foreignId('employed_id')->nullable()->constrained('employeds');
      $table->foreignId('profession_group_id')->nullable()->constrained('profession_groups');
      $table->timestamps();
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('participant_profiles');
  }
};
