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
        Schema::create('sites', function (Blueprint $table) {
            $table->id()->comment('ID');
            $table->unsignedBigInteger('company_id')->nullable()->comment('元請けID');
            $table->foreign('company_id')->references('id')->on('companies')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->boolean('is_done')->default(false)->comment('作業完了済');
            $table->string('name')->nullable()->comment('作業所名');
            $table->date('period_st')->nullable()->comment('工期開始');
            $table->date('period_ed')->nullable()->comment('工期終了');
            $table->string('zip')->nullable()->comment('郵便番号');
            $table->string('pref')->nullable()->comment('都道府県');
            $table->string('address1')->nullable()->comment('市区町村');
            $table->string('address2')->nullable()->comment('町名番地');
            $table->text('memo')->nullable()->comment('メモ');
            $table->timestamps();
        });

        DB::statement("ALTER TABLE sites COMMENT '作業所';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sites');
    }
};
