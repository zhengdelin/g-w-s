<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePoolImgsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pool_imgs', function (Blueprint $table) {
            $table->id();
            $table->integer('pool_id');
            $table->string('cr_main_img',200);
            $table->string('wp_main_img',200);
            $table->string('cr_thum_on_img',200);
            $table->string('cr_thum_off_img',200);
            $table->string('wp_thum_on_img',200);
            $table->string('wp_thum_off_img',200);
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
        Schema::dropIfExists('pool_imgs');
    }
}
