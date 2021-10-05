<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comment',function(Blueprint $table){
            $table->engine = 'InnoDB';
            $table->increments('id');           //int(10)主键
            $table->integer('news_id');        //int(11)
            $table->biginteger('news_idtwo'); //int(20)
            $table->datetime('time');       //  -mm-dd h:i:s;
            $table->integer('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comments');
    }
}
