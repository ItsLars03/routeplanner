<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('stop_vehicle_time_slot', function (Blueprint $table) {
            $table->integer('sequence')->nullable()->after('stop_id')->index();
            $table->timestamp('arrived_at')->nullable()->after('sequence')->index();
        });
    }

    public function down()
    {
        Schema::table('stop_vehicle_time_slot', function (Blueprint $table) {
            $table->dropColumn(['sequence', 'arrived_at']);
        });
    }
};
