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
        Schema::create('clients', function (Blueprint $table) {
            $table->id()->comment('ID');
            $table->boolean('is_enabled')->default(false)->comment('有効無効');
            $table->string('name')->nullable()->comment('社名');
            $table->string('email')->unique()->comment('メールアドレス');
            $table->string('password');
            $table->rememberToken();
            $table->string('zip')->nullable()->comment('郵便番号');
            $table->string('pref')->nullable()->comment('都道府県');
            $table->string('address1')->nullable()->comment('市区町村');
            $table->string('address2')->nullable()->comment('町名番地');
            $table->string('tel')->nullable()->comment('電話番号');
            $table->text('memo')->nullable()->comment('メモ');
            $table->timestamps();
        });

        DB::statement("ALTER TABLE clients COMMENT '企業';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('companies');
    }
};
