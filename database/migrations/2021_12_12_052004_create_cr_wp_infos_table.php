<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrWpInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cr_wp_infos', function (Blueprint $table) {
            $table->id();
            $table->String('name',10);
            $table->integer('star');
            $table->String('kind',2);
            $table->String('attribute',2)->nullable();
            $table->String('img',200)->nullable();
            $table->String('avatar',200)->nullable();
            $table->String('inv_avatar',200)->nullable();
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
        Schema::dropIfExists('cr_wp_infos');
    }
}
