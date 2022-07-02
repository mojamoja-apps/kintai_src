
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
        Schema::create('kintai_rirekis', function (Blueprint $table) {
            $table->id();
            $table->date('day')->nullable()->comment('日');
            $table->unsignedBigInteger('client_id')->comment('企業ID');
            $table->foreign('client_id')->references('id')->on('clients')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->unsignedBigInteger('employee_id')->comment('社員ID');
            $table->foreign('employee_id')->references('id')->on('employees')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->time("time")->nullable()->comment("勤務開始");
            $table->boolean('midnight')->default(false)->comment('深夜残業 前日分の退勤として打刻チェック');
            $table->double('lat', 10, 6)->nullable()->comment('緯度');
            $table->double('lon', 10, 6)->nullable()->comment('経度');

            $table->timestamps();
        });

        DB::statement("ALTER TABLE kintai_rirekis COMMENT '勤怠打刻履歴';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kintai_rirekis');
    }
};
