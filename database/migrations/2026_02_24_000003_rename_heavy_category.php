<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Normalize known "heavy" category variants to "Power Tools"
        DB::table('equipment')
            ->whereRaw('LOWER(category) IN (?,?,?)', ['heavy', 'heavy equipment', 'heavy equipments'])
            ->update(['category' => 'Power Tools']);
    }

    public function down()
    {
        // Revert Power Tools back to Heavy (best-effort)
        DB::table('equipment')
            ->where('category', 'Power Tools')
            ->update(['category' => 'Heavy']);
    }
};
