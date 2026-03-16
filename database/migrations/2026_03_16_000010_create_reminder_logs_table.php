<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReminderLogsTable extends Migration
{
    public function up()
    {
        Schema::create('reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_request_item_id');
            $table->integer('days'); // relative days (e.g. 4, 1, 0, -3)
            $table->timestamps();

            $table->index(['inventory_request_item_id', 'days']);
            $table->foreign('inventory_request_item_id')->references('id')->on('inventory_request_items')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reminder_logs');
    }
}
