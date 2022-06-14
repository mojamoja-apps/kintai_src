<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kintais', function (Blueprint $table) {
            $table->date('day')->nullable()->comment('日');
            $table->unsignedBigInteger('client_id')->comment('企業ID');
            $table->foreign('client_id')->references('id')->on('clients')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->unsignedBigInteger('employee_id')->comment('社員ID');
            $table->foreign('employee_id')->references('id')->on('employees')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->time("date_st")->nullable()->comment("勤務開始");
            $table->time("date_ed")->nullable()->comment("勤務終了");
            $table->time("date_rest_st_1")->nullable()->comment("休憩開始1");
            $table->time("date_rest_ed_1")->nullable()->comment("休憩終了1");
            $table->time("date_rest_st_2")->nullable()->comment("休憩開始2");
            $table->time("date_rest_ed_2")->nullable()->comment("休憩終了2");

            $table->timestamps();
            $table->primary(['day', 'client_id', 'employee_id']); // 日、企業、社員IDでキー
        });

        DB::statement("ALTER TABLE kintais COMMENT '勤怠';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('report_drivers');
    }
};
