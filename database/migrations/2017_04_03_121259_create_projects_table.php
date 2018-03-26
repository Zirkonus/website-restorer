<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->unsigned();
            $table->string('domain');
            $table->string('niche')->nullable();
            $table->integer('fetch_level_deep');
            $table->string('ftp_address')->nullable();
            $table->integer('ftp_port')->nullable();
            $table->string('ftp_username')->nullable();
            $table->string('ftp_password', 1000)->nullable();
            $table->string('ftp_folder')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return voids
     */
    public function down()
    {
        Schema::drop('projects');
    }
}
