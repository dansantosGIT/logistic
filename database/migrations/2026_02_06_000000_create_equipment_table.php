<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('serial')->unique();
            $table->string('category')->nullable();
            $table->string('type')->nullable();
            $table->integer('quantity')->default(1);
            $table->string('location')->nullable();
            $table->string('tag')->nullable();
            $table->timestamp('date_added')->nullable();
            $table->string('image_path')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('equipment');
    }
};
