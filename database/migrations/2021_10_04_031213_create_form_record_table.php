<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormRecordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('form_record', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('form_name',100)->comment('表名');
            $table->string('form_name_ind',100)->comment('表代码');
            $table->boolean('status')->default(0)->comment('状态，0：失败，1：成功');
            $table->string('error_msg',200)->comment('失败原因');
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
        Schema::dropIfExists('form_record');
    }
}
