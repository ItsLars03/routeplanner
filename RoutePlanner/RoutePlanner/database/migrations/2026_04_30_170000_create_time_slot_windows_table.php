<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_slot_windows', function (Blueprint $table) {
            $table->id();
            $table->string('slot_key', 20)->unique();
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        foreach (\App\Models\TimeSlotWindow::DEFAULT_WINDOWS as $slotKey => [$startTime, $endTime]) {
            DB::table('time_slot_windows')->insert([
                'slot_key' => $slotKey,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'is_active' => true,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('time_slot_windows');
    }
};