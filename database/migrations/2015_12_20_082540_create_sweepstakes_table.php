<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSweepstakesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sweepstakes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('item_id');
            $table->tinyInteger('win_num');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->integer('entry_num');
            $table->string('win_user_ids');
            $table->boolean('delete_flag');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sweepstakes');
    }
}
