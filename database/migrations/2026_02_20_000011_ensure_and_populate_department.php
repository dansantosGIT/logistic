<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class EnsureAndPopulateDepartment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('inventory_requests', 'department')) {
            Schema::table('inventory_requests', function (Blueprint $table) {
                $table->string('department')->nullable()->after('role');
            });
        }

        $map = [
            'employee' => 'Admin & Training',
            'operations' => 'Operations',
            'training' => 'Training',
            'volunteer' => 'Volunteer',
            'cedoc' => 'Cedoc',
            'planning' => 'Planning',
            'admin' => 'Admin'
        ];

        foreach ($map as $role => $dept) {
            DB::table('inventory_requests')
                ->whereNull('department')
                ->whereRaw('LOWER(role) = ?', [$role])
                ->update(['department' => $dept]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Intentionally left blank to avoid accidentally dropping a column that may have been
        // present before this migration ran. If you want to remove the column, do so manually.
    }
}
