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
        Schema::create('damage_reports', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('vehicle_id')->nullable()->index();
            $table->unsignedBigInteger('driver_id')->nullable()->index();

            $table->date('reported_date');
            $table->string('location')->nullable();
            $table->string('damage_type', 120);
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->text('description');
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->enum('status', ['open', 'in_progress', 'resolved', 'rejected'])->default('open');
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('damage_reports');
    }
};
