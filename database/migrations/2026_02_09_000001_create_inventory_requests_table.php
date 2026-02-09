<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventory_requests', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_name')->nullable();
            $table->string('requester')->nullable();
            $table->unsignedBigInteger('requester_user_id')->nullable();
            $table->integer('quantity')->default(1);
            $table->string('role')->nullable();
            $table->text('reason')->nullable();
            $table->date('return_date')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('handled_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_requests');
    }
};
