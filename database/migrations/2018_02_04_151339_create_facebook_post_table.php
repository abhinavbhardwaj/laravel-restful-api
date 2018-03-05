<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFacebookPostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facebook_post', function (Blueprint $table) {
            $table->increments('id');
            $table->string('fb_id');
            $table->string('title')->nullable();
            $table->string('message')->nullable();
            $table->string('link')->nullable();
            $table->string('picture')->nullable();
            $table->mediumText('description')->nullable(); 
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');            
            $table->tinyInteger('status')->default('1');
            $table->timestamps();
            $table->index('fb_id');
            $table->index('title');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('facebook_post');
    }
}
