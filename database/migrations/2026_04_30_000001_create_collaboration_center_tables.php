<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('collaboration_news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('collaboration_suggestions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('open');
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();
        });

        Schema::create('collaboration_todos', function (Blueprint $table) {
            $table->id();
            $table->text('task');
            $table->boolean('is_completed')->default(false);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('suggestion_id')->nullable()->constrained('collaboration_suggestions')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('collaboration_suggestion_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('suggestion_id')->constrained('collaboration_suggestions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('vote');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['suggestion_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collaboration_suggestion_votes');
        Schema::dropIfExists('collaboration_todos');
        Schema::dropIfExists('collaboration_suggestions');
        Schema::dropIfExists('collaboration_news');
    }
};
