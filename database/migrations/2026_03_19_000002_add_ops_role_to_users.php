<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'ops_role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('ops_role')->nullable()->after('role');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'ops_role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('ops_role');
            });
        }
    }
};
