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
            $table->string('name')->nullable()->comment('作業員名');
            $table->string('kana')->nullable()->comment('作業員名かな');
            $table->unsignedBigInteger('client_id')->nullable()->comment('企業ID');
            $table->foreign('client_id')->references('id')->on('clients')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->text('memo')->nullable()->comment('メモ');
            $table->timestamps();
        });

        DB::statement("ALTER TABLE employees COMMENT '社員';");
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
