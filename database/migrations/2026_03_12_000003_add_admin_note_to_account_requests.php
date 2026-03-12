<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdminNoteToAccountRequests extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('account_requests', 'admin_note')) {
            Schema::table('account_requests', function (Blueprint $table) {
                $table->text('admin_note')->nullable()->after('justification');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('account_requests', 'admin_note')) {
            Schema::table('account_requests', function (Blueprint $table) {
                $table->dropColumn('admin_note');
            });
        }
    }
}
