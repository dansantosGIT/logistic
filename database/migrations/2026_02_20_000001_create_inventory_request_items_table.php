<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_request_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_request_id');
            $table->unsignedBigInteger('equipment_id');
            $table->integer('quantity')->default(1);
            $table->text('notes')->nullable();
            $table->date('return_date')->nullable();
            $table->string('location')->nullable();
            $table->timestamps();

            $table->foreign('inventory_request_id')->references('id')->on('inventory_requests')->onDelete('cascade');
            $table->foreign('equipment_id')->references('id')->on('equipment')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_request_items');
    }
};
