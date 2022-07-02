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
            $table->id();
            $table->date('day')->nullable()->comment('日');
            $table->unsignedBigInteger('client_id')->comment('企業ID');
            $table->foreign('client_id')->references('id')->on('clients')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->unsignedBigInteger('employee_id')->comment('社員ID');
            $table->foreign('employee_id')->references('id')->on('employees')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->time("time_1")->nullable()->comment("勤務開始");
            $table->time("time_2")->nullable()->comment("休憩開始1");
            $table->time("time_3")->nullable()->comment("休憩終了1");
            $table->time("time_4")->nullable()->comment("休憩開始2");
            $table->time("time_5")->nullable()->comment("休憩終了2");
            $table->time("time_6")->nullable()->comment("勤務終了");
            $table->boolean('midnight')->default(false)->comment('深夜残業 前日分の退勤として打刻チェック');
            $table->double('lat', 10, 6)->nullable()->comment('緯度');
            $table->double('lon', 10, 6)->nullable()->comment('経度');


            $table->timestamps();
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
        Schema::dropIfExists('kintais');
    }
};
