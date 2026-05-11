<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('stop_vehicle_time_slot', function (Blueprint $table) {
            $table->boolean('delivered_late')->nullable()->after('arrived_at');
        });
    }

    public function down()
    {
        Schema::table('stop_vehicle_time_slot', function (Blueprint $table) {
            $table->dropColumn(['delivered_late']);
        });
    }
};
