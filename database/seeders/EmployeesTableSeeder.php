<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        \DB::table('employees')->insert([
            [
                'name' => '相川',
                'kana' => 'あいかわ',
                'is_enabled' => true,
                'client_id' => 1,
                'memo' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        ]);
        \DB::table('employees')->insert([
            [
                'name' => '今田',
                'kana' => 'いまだ',
                'is_enabled' => true,
                'client_id' => 1,
                'memo' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        ]);
        \DB::table('employees')->insert([
            [
                'name' => '内田',
                'kana' => 'うちだ',
                'is_enabled' => true,
                'client_id' => 1,
                'memo' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        ]);
        \DB::table('employees')->insert([
            [
                'name' => '榎本',
                'kana' => 'えのもと',
                'is_enabled' => true,
                'client_id' => 1,
                'memo' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        ]);
        \DB::table('employees')->insert([
            [
                'name' => '大木',
                'kana' => 'おおき',
                'is_enabled' => true,
                'client_id' => 1,
                'memo' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        ]);
        \DB::table('employees')->insert([
            [
                'name' => '香山',
                'kana' => 'かやま',
                'is_enabled' => true,
                'client_id' => 1,
                'memo' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        ]);


        // for ($ix = 1; $ix <= 10; $ix++) {
        //     $dispix = sprintf('%03d', $ix);
        //     \DB::table('employees')->insert([
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
        //\App\Models\Employee::factory(20)->create();  // 10個作成ね！
    }
}
