<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class JobsRefactor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->text('data');
            $table->integer('user_id');
            
            $table->index(['type', 'user_id', 'is_finish']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn('data');
            $table->dropColumn('user_id');
            
            $table->dropIndex('jobs_type_user_id_is_finish_index');
        });
    }
}
