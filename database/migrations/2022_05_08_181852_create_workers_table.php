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
        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable()->comment('作業員名');
            $table->string('kana')->nullable()->comment('作業員名かな');
            $table->integer('style')->unsigned()->nullable()->comment('労働形態 正社員・バイト');
            $table->integer('belongs')->unsigned()->nullable()->comment('所属会社');
            $table->boolean('insurance')->nullable()->comment('社会保険ありなし');
            $table->text('memo')->nullable()->comment('メモ');
            $table->timestamps();
        });

        DB::statement("ALTER TABLE sites COMMENT '作業員';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workers');
    }
};
