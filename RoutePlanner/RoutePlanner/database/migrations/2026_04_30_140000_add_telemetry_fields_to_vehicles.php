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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->decimal('last_latitude', 10, 8)->nullable()->after('api_token');
            $table->decimal('last_longitude', 11, 8)->nullable()->after('last_latitude');
            $table->decimal('last_temperature', 5, 2)->nullable()->after('last_longitude');
            $table->timestamp('last_telemetry_at')->nullable()->after('last_temperature');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['last_latitude', 'last_longitude', 'last_temperature', 'last_telemetry_at']);
        });
    }
};
