<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (Schema::hasTable('account_requests') && !Schema::hasColumn('account_requests', 'justification')) {
            Schema::table('account_requests', function (Blueprint $table) {
                $table->text('justification')->nullable()->after('requested_role');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('account_requests') && Schema::hasColumn('account_requests', 'justification')) {
            Schema::table('account_requests', function (Blueprint $table) {
                $table->dropColumn('justification');
            });
        }
    }
};
