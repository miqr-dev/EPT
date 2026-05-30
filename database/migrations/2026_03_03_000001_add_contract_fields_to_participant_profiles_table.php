<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('participant_profiles', function (Blueprint $table) {
            $table->string('course')->nullable()->after('profession_group_id');
            $table->string('location')->nullable()->after('course');
            $table->date('measure_start')->nullable()->after('location');
            $table->date('measure_end')->nullable()->after('measure_start');
            $table->string('measure_time')->nullable()->after('measure_end');
            $table->string('street')->nullable()->after('measure_time');
            $table->string('zip', 20)->nullable()->after('street');
            $table->string('city_name')->nullable()->after('zip');
            $table->string('telephone')->nullable()->after('city_name');
            $table->string('cost_bearer')->nullable()->after('telephone');
            $table->timestamp('contract_imported_at')->nullable()->after('cost_bearer');
        });
    }

    public function down(): void
    {
        Schema::table('participant_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'course',
                'location',
                'measure_start',
                'measure_end',
                'measure_time',
                'street',
                'zip',
                'city_name',
                'telephone',
                'cost_bearer',
                'contract_imported_at',
            ]);
        });
    }
};
