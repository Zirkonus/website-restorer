<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('pid');
            $table->bigInteger('version_id')->unsigned();
            $table->string('file_name');
            $table->string('file_ext');
            $table->string('file_content_type');
            $table->integer('file_size');
            $table->string('title');
            $table->smallInteger('is_local');
            $table->string('web_path');
            $table->string('storage_path');
            $table->string('replace_url');
            $table->string('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('files');
    }
}
