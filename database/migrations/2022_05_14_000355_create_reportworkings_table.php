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
        Schema::create('report_workings', function (Blueprint $table) {
            $table->unsignedBigInteger('report_id')->nullable()->comment('作業証明書ID');
            $table->foreign('report_id')->references('id')->on('reports')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->integer('no')->unsigned()->comment('作業員連番');
            $table->unsignedBigInteger('worker_id')->nullable()->comment('作業員ID');
            $table->foreign('worker_id')->references('id')->on('workers')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->integer('tobidoko')->unsigned()->nullable()->comment('鳶工 or 土工');
            $table->float('sozan')->nullable()->comment('早残');

            $table->timestamps();
            $table->primary(['report_id', 'no']); // 作業証明書、連番でキー
        });

        DB::statement("ALTER TABLE sites COMMENT '作業証明書 作業員の出勤';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('report_workings');
    }
};
