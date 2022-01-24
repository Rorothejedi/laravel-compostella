<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained('albums')->onDelete('cascade');
            $table->integer('album_order');
            $table->text('text')->nullable();
            $table->boolean('main_album_image')->default(0);
            $table->string('path');
            $table->integer('width');
            $table->integer('height');
            $table->string('thumbnail_path');
            $table->integer('thumbnail_width');
            $table->integer('thumbnail_height');
            $table->string('cover_path');
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
        Schema::dropIfExists('images');
    }
}
