<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_step_statuses', function (Blueprint $table) {
            $table->timestamp('force_finish_requested_at')->nullable()->after('paused_from_status');
            $table->timestamp('force_finish_deadline')->nullable()->after('force_finish_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('exam_step_statuses', function (Blueprint $table) {
            $table->dropColumn(['force_finish_deadline', 'force_finish_requested_at']);
        });
    }
};
