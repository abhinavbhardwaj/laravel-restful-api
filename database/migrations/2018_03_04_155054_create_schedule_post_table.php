<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSchedulePostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedule_post', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');   
            $table->string('account_type')->nullable();
            $table->mediumText('auth_token')->nullable();
            $table->mediumText('auth_secret')->nullable();
            $table->mediumText('post_data')->nullable();
            $table->dateTime('schedule_date')->useCurrent();
            $table->enum('status', ['1', '2','3'])->default('1')->comment = "1=pending, 2 = done, 3 = inactive";
            $table->timestamps();
            $table->index('id');
            $table->index('schedule_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedule_post');
    }
}
