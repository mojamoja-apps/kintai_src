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
        Schema::table('clients', function (Blueprint $table) {
            $table->boolean('zangyo_flg')->default(false)->comment('残業表示 ありなし');
            $table->integer('kinmu_limit_hour')->unsigned()->default(8)->comment('MAX勤務時間 これ以上は残業扱い');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('zangyo_flg');
            $table->dropColumn('kinmu_limit_hour');
        });
    }
};
