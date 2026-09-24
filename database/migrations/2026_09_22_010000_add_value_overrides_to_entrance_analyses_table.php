<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('entrance_analyses', 'value_overrides')) {
            return;
        }

        Schema::table('entrance_analyses', function (Blueprint $table) {
            $table->json('value_overrides')->nullable()->after('mark_overrides');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('entrance_analyses', 'value_overrides')) {
            return;
        }

        Schema::table('entrance_analyses', function (Blueprint $table) {
            $table->dropColumn('value_overrides');
        });
    }
};
