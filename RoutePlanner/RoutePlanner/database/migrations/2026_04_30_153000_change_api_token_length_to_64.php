<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `vehicles` MODIFY COLUMN `api_token` VARCHAR(64) NULL;');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE vehicles ALTER COLUMN api_token TYPE VARCHAR(64);');
        } elseif ($driver === 'sqlite') {
            // SQLite cannot modify column types easily; instruct user to run manual steps.
            throw new \RuntimeException('SQLite detected: please adjust the `api_token` column manually (SQLite does not support altering column length via ALTER).');
        } else {
            throw new \RuntimeException('Unsupported DB driver: ' . $driver . '. Please alter `api_token` column to VARCHAR(64) manually.');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `vehicles` MODIFY COLUMN `api_token` VARCHAR(60) NULL;');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE vehicles ALTER COLUMN api_token TYPE VARCHAR(60);');
        } elseif ($driver === 'sqlite') {
            throw new \RuntimeException('SQLite detected: please revert the `api_token` column manually.');
        } else {
            throw new \RuntimeException('Unsupported DB driver: ' . $driver . '. Please revert `api_token` column to VARCHAR(60) manually.');
        }
    }
};
