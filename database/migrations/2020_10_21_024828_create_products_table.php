<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');

            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->integer('target_funding')->nullable();
            $table->dateTime('target_end')->nullable();

            $table->text('photo_path')->nullable();
            $table->text('video_path')->nullable();

            $table->softDeletes();
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
        Schema::dropIfExists('products');
    }
}
