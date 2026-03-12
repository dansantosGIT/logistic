<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE vehicle_maintenances MODIFY task TEXT NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE vehicle_maintenances MODIFY task VARCHAR(255) NOT NULL');
    }
};
