<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_step_statuses', function (Blueprint $table) {
            $table->integer('time_remaining_seconds')->nullable()->after('grace_period_seconds');
            $table->string('paused_from_status')->nullable()->after('status');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `exam_step_statuses` MODIFY COLUMN `status` ENUM('not_started','in_progress','waiting','paused','completed','broken') DEFAULT 'not_started'");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `exam_step_statuses` MODIFY COLUMN `status` ENUM('not_started','in_progress','waiting','completed') DEFAULT 'not_started'");
        }

        Schema::table('exam_step_statuses', function (Blueprint $table) {
            $table->dropColumn(['time_remaining_seconds', 'paused_from_status']);
        });
    }
};
