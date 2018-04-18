<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableEmailChecks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_checks', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('email');
			$table->string('token');
			$table->timestamp('created_at');
			$table->timestamp('updated_at');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('email_checks');
    }
}
