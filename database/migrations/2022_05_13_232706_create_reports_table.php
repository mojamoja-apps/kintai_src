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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->date('day')->nullable()->comment('作業日');
            $table->unsignedBigInteger('company_id')->nullable()->comment('元請けID');
            $table->foreign('company_id')->references('id')->on('companies')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->unsignedBigInteger('site_id')->nullable()->comment('作業所ID');
            $table->foreign('site_id')->references('id')->on('sites')->onUpdate('CASCADE')->onDelete('SET NULL');

            $names = [];
            $names[1] = '仮設工事';
            $names[2] = 'コンクリート工事';
            $names[3] = '土工事';
            $names[4] = '常傭工事①';
            $names[5] = '常傭工事②';
            for ($ix = 1; $ix <= 5; $ix++) {
                $table->integer("koji_{$ix}_kbn")->nullable()->comment("{$names[$ix]}-区分");
                $table->text("koji_{$ix}_memo")->nullable()->comment("{$names[$ix]}-メモ");

                for ($iy = 1; $iy <= 5; $iy++) {
                    $table->time("koji_{$ix}_tobi_{$iy}_sttime")->nullable()->comment("{$names[$ix]}-鳶{$iy}工稼働時間開始");
                    $table->time("koji_{$ix}_tobi_{$iy}_edtime")->nullable()->comment("{$names[$ix]}-鳶{$iy}工稼働時間終了");
                    $table->integer("koji_{$ix}_tobi_{$iy}_num")->nullable()->comment("{$names[$ix]}-鳶{$iy}工員数");
                    $table->float("koji_{$ix}_tobi_{$iy}_sozan")->nullable()->comment("{$names[$ix]}-鳶{$iy}工早残");
                    $table->time("koji_{$ix}_doko_{$iy}_sttime")->nullable()->comment("{$names[$ix]}-土工{$iy}稼働時間開始");
                    $table->time("koji_{$ix}_doko_{$iy}_edtime")->nullable()->comment("{$names[$ix]}-土工{$iy}稼働時間開始");
                    $table->integer("koji_{$ix}_doko_{$iy}_num")->nullable()->comment("{$names[$ix]}-土工{$iy}員数");
                    $table->float("koji_{$ix}_doko_{$iy}_sozan")->nullable()->comment("{$names[$ix]}-土工{$iy}早残");
                }

                $table->float("koji_{$ix}_dasetu")->nullable()->comment("{$names[$ix]}-総打設数量");
                for ($iy = 1; $iy <= 5; $iy++) {
                    $table->time("koji_{$ix}_car_{$iy}_sttime")->nullable()->comment("{$names[$ix]}-車両{$iy}稼働時間開始");
                    $table->time("koji_{$ix}_car_{$iy}_edtime")->nullable()->comment("{$names[$ix]}-車両{$iy}稼働時間終了");
                    $table->integer("koji_{$ix}_car_{$iy}_ton")->nullable()->comment("{$names[$ix]}-車両{$iy}種類");
                    $table->integer("koji_{$ix}_car_{$iy}_num")->nullable()->comment("{$names[$ix]}-車両{$iy}台数");
                    $table->float("koji_{$ix}_car_{$iy}_sozan")->nullable()->comment("{$names[$ix]}-車両{$iy}早残");
                }
            }

            $table->timestamps();
        });

        DB::statement("ALTER TABLE sites COMMENT '作業証明書';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reports');
    }
};
