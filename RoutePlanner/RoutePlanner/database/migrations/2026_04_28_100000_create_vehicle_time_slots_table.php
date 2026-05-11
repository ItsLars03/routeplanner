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
        Schema::create('vehicle_time_slots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id')->index();
            $table->unsignedBigInteger('driver_id')->nullable()->index();
            $table->date('slot_date')->index();
            $table->string('slot_key', 20);
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();

            $table->unique(['vehicle_id', 'slot_date', 'slot_key'], 'vehicle_slot_unique');
            $table->unique(['slot_date', 'slot_key', 'driver_id'], 'driver_slot_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_time_slots');
    }
};
