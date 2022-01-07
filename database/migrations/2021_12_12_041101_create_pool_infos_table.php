<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePoolInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pool_infos', function (Blueprint $table) {
            $table->id();
            $table->string('version',5);
            $table->string('pool_name',50);
            $table->string('cr_name',10);
            
            $table->integer('five_std_id');
            $table->integer('four_std_id');
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
        Schema::dropIfExists('pool_infos');
    }
}
