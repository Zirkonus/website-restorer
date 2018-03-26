<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->unsigned();
            $table->bigInteger('project_id')->unsigned();
            $table->string('message', 500);
            $table->string('type');
            $table->smallInteger('is_view');
            $table->timestamps();
        });

        Schema::table('projects', function($table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('versions', function($table) {
            $table->foreign('project_id')->references('id')->on('projects');
        });

        Schema::table('files', function($table) {
            $table->foreign('version_id')->references('id')->on('versions');
        });

        Schema::table('history', function($table) {
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::table('history', function($table) {
            $table->foreign('project_id')->references('id')->on('projects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('history');
    }
}
