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
            $table->text('data')->nullable();
            $table->integer('user_id')->nullable();
            
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
            $table->dropColumn(['data', 'user_id']);
        });
        
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropIndex('jobs_type_user_id_is_finish_index');
        });
    }
}
