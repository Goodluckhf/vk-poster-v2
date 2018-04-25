<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class JobRefactorPolymorph extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropIndex('jobs_type_user_id_is_finish_index');
        });
        
        Schema::table('jobs', function (Blueprint $table) {
            $table->string('job_type')->nullable();
            $table->integer('job_id')->nullable();
            $table->dropColumn(['type', 'data']);
        });
        
        Schema::create('group_seek_jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('group_id');
            $table->integer('count');
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
            $table->dropColumn(['job_type', 'job_id']);
            $table->text('data')->nullable();
            $table->enum('type', ['post', 'seek', 'like_seek'])->nullable();
            $table->index(['type', 'user_id', 'is_finish']);
        });
        
        Schema::drop('group_seek_jobs');
    }
}
