<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WorkersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // for ($ix = 1; $ix <= 10; $ix++) {
        //     $dispix = sprintf('%03d', $ix);
        //     \DB::table('workers')->insert([
        //         [
        //             'name' => '自社 ' . $dispix . '作業員',
        //             'belongs' => 1,
        //             'style' => rand(1,2),
        //             'insurance' => rand(1,2),
        //             'created_at' => date('Y-m-d H:i:s'),
        //             'updated_at' => date('Y-m-d H:i:s'),
        //         ]
        //     ]);
        // }

        // for ($ix = 1; $ix <= 10; $ix++) {
        //     $dispix = sprintf('%03d', $ix);
        //     \DB::table('workers')->insert([
        //         [
        //             'name' => '三浦組 ' . $dispix . '作業員',
        //             'belongs' => 1,
        //             'style' => rand(1,2),
        //             'insurance' => rand(1,2),
        //             'created_at' => date('Y-m-d H:i:s'),
        //             'updated_at' => date('Y-m-d H:i:s'),
        //         ]
        //     ]);
        // }

        //\App\Models\Worker::truncate();  // 既存データを削除
        \App\Models\Worker::factory(20)->create();  // 10個作成ね！
    }
}
