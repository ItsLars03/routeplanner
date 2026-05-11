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
        Schema::create('stop_vehicle_time_slot', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_time_slot_id')->index();
            $table->unsignedBigInteger('stop_id')->index();
            $table->timestamps();

            $table->unique(['vehicle_time_slot_id', 'stop_id'], 'slot_stop_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stop_vehicle_time_slot');
    }
};
