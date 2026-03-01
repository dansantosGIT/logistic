<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('inventory_requests', function (Blueprint $table) {
            $table->string('printed_pdf_path')->nullable()->after('status');
            $table->unsignedBigInteger('printed_by')->nullable()->after('printed_pdf_path');
            $table->timestamp('printed_at')->nullable()->after('printed_by');

            $table->string('signed_scan_path')->nullable()->after('printed_at');
            $table->unsignedBigInteger('signed_by')->nullable()->after('signed_scan_path');
            $table->timestamp('signed_at')->nullable()->after('signed_by');
        });
    }

    public function down()
    {
        Schema::table('inventory_requests', function (Blueprint $table) {
            $table->dropColumn(['printed_pdf_path','printed_by','printed_at','signed_scan_path','signed_by','signed_at']);
        });
    }
};
