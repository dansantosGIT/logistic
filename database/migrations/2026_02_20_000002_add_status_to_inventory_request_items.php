<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('inventory_request_items', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('location');
            $table->integer('issued_quantity')->default(0)->after('status');
            $table->unsignedBigInteger('handled_by')->nullable()->after('issued_quantity');
            $table->timestamp('handled_at')->nullable()->after('handled_by');
        });
    }

    public function down()
    {
        Schema::table('inventory_request_items', function (Blueprint $table) {
            $table->dropColumn(['status','issued_quantity','handled_by','handled_at']);
        });
    }
};
