<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_step_statuses', function (Blueprint $table) {
            $table->json('progress')->nullable()->after('completed_at');
            $table->timestamp('last_saved_at')->nullable()->after('progress');
        });
    }

    public function down(): void
    {
        Schema::table('exam_step_statuses', function (Blueprint $table) {
            $table->dropColumn(['progress', 'last_saved_at']);
        });
    }
};
