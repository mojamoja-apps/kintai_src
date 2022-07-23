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
        Schema::table('kintais', function (Blueprint $table) {
            $table->float('zangyo_hour')->default(0)->comment('残業時間');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kintais', function (Blueprint $table) {
            $table->dropColumn('zangyo_hour');
        });
    }
};
