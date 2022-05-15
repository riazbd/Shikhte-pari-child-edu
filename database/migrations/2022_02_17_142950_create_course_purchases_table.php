<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoursePurchasesTable extends Migration
{

    public function up()
    {
        Schema::create('course_purchases', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('course_id');
            $table->string('status')->default('purchased');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('course_purchases');
    }
}
