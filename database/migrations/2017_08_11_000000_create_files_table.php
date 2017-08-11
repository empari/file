<?php

use Illuminate\Support\Facades\Schema;
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
            $table->uuid('id'); $table->primary('id');
            $table->string('name', 255)->index();
            $table->string('slug', 255)->unique();
            $table->string('original_name', 255)->nullable();
            $table->string('extension', 4)->nullable();
            $table->string('type', 255)->nullable();
            $table->string('mime_type', 255)->nullable();
            $table->string('drive', 255)->default('s3');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->dateTime('last_modified')->nullable;
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files');
    }
}