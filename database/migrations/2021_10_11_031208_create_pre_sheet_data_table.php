<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreSheetDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pre_sheet_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('school',100)->comment('学校名');
            $table->enum('school_type',['kindergarten','primarySchool','juniorMiddleSchool','highSchool','secondaryVocationalSchool','specialSchool','nineYearCon'])->default('kindergarten')->comment('学校类型，kindergarten：幼儿园，primarySchool：小学，juniorMiddleSchool：初中，highSchool：普高，SecondaryVocationalSchool：中职，specialSchool：特殊教育');
            $table->string('found_divisor',20)->nullable()->comment('实际值除数');
            $table->string('found_divider',20)->nullable()->comment('实际值被除数');
            $table->string('report_hash',100)->comment('批次');
            $table->string('found_ind',50)->comment('指标代码');
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
        Schema::dropIfExists('pre_sheet_data');
    }
}
