<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSheetRecordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sheet_record', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('form_id')->comment('关联上传表');
            $table->string('school',100)->comment('学校名');
            $table->enum('school_type',['kindergarten','primarySchool','juniorMiddleSchool','highSchool','secondaryVocationalSchool','specialSchool','nineYearCon'])->default('kindergarten')->comment('学校类型，kindergarten：幼儿园，primarySchool：小学，juniorMiddleSchool：初中，highSchool：普高，SecondaryVocationalSchool：中职，specialSchool：特殊教育');
            $table->string('basic_val',20)->comment('基本值');
            $table->string('found_val',20)->comment('实际值');
            $table->string('standard_val',20)->comment('达标值');
            $table->boolean('is_standard')->default(0)->comment('达标，0：未达标，1：已达标');
            $table->string('found_ind',50)->comment('指标代码');
            $table->string('found_name',100)->comment('指标名');
            $table->enum('report_type',['modern','balance'])->default('modern')->comment('类型，modern：现代化指标，balance：均衡发展');
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
        Schema::dropIfExists('sheet_record');
    }
}
