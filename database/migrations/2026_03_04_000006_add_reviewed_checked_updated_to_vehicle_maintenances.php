<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_maintenances', function (Blueprint $table) {
            $table->timestamp('reviewed_at')->nullable()->after('due_date');
            $table->timestamp('checked_at')->nullable()->after('reviewed_at');
            $table->timestamp('updated_marker_at')->nullable()->after('checked_at');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_maintenances', function (Blueprint $table) {
            $table->dropColumn(['reviewed_at', 'checked_at', 'updated_marker_at']);
        });
    }
};
