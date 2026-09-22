<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('entrance_analyses', 'mark_overrides')) {
            return;
        }

        Schema::table('entrance_analyses', function (Blueprint $table) {
            $table->json('mark_overrides')->nullable()->after('remarks');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('entrance_analyses', 'mark_overrides')) {
            return;
        }

        Schema::table('entrance_analyses', function (Blueprint $table) {
            $table->dropColumn('mark_overrides');
        });
    }
};
