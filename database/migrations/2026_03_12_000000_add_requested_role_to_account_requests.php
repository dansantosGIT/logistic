<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRequestedRoleToAccountRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('account_requests')) {
            return;
        }

        Schema::table('account_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('account_requests', 'requested_role')) {
                $table->string('requested_role')->nullable()->default('requestor')->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('account_requests')) {
            return;
        }

        Schema::table('account_requests', function (Blueprint $table) {
            if (Schema::hasColumn('account_requests', 'requested_role')) {
                $table->dropColumn('requested_role');
            }
        });
    }
}
