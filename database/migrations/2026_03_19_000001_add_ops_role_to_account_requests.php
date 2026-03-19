<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (Schema::hasTable('account_requests') && !Schema::hasColumn('account_requests', 'ops_role')) {
            Schema::table('account_requests', function (Blueprint $table) {
                $table->string('ops_role')->nullable()->after('requested_role');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('account_requests') && Schema::hasColumn('account_requests', 'ops_role')) {
            Schema::table('account_requests', function (Blueprint $table) {
                $table->dropColumn('ops_role');
            });
        }
    }
};
