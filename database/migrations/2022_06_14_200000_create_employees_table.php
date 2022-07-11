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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable()->comment('企業ID');
            $table->foreign('client_id')->references('id')->on('clients')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->boolean('is_enabled')->default(false)->comment('有効無効');
            $table->string('code')->nullable()->comment('従業員コード');
            $table->string('name')->nullable()->comment('従業員名');
            $table->string('kana')->nullable()->comment('従業員名かな');
            $table->text('memo')->nullable()->comment('メモ');
            $table->integer('order')->unsigned()->default(999999)->comment('表示順');
            $table->timestamps();
        });

        DB::statement("ALTER TABLE employees COMMENT '従業員';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
};
