<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('type');
            $table->unsignedSmallInteger('year')->nullable()->after('brand');
            $table->boolean('is_firetruck')->default(false)->after('year');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['brand', 'year', 'is_firetruck']);
        });
    }
};
