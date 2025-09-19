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
        Schema::table('exam_step_statuses', function (Blueprint $table) {
            $table->boolean('pause_enabled')->default(false)->after('time_remaining_seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_step_statuses', function (Blueprint $table) {
            $table->dropColumn('pause_enabled');
        });
    }
};
