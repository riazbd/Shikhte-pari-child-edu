<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseAssignsTable extends Migration
{

    public function up()
    {
        Schema::create('course_assigns', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('assigned_by');
            $table->integer('course_id');
            $table->string('status')->default('assigned');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('course_assigns');
    }
}
