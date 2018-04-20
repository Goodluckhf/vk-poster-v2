<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewTableGifs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gifs', function (Blueprint $table) {
            /*
            $table->bigIncrements('id');
            $table->bigInteger('doc_id');
            $table->bigInteger('owner_id');
            $table->string('title');
            $table->string('url');
            $table->string('thumb');
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
            */
            //test2
            $table->bigInteger('owner_user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::drop('gifs');
    }
}
