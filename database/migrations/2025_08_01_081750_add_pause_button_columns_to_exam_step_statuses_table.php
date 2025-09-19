<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_step_statuses', function (Blueprint $table) {
            $table->boolean('pause_button_enabled')->default(false)->after('time_remaining_seconds');
            $table->json('paused_results')->nullable()->after('pause_button_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('exam_step_statuses', function (Blueprint $table) {
            $table->dropColumn(['paused_results', 'pause_button_enabled']);
        });
    }
};
