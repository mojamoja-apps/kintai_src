<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('clients')->insert([
            [
                'id' => 2,
                'is_enabled' => false,
                'name' => 'テスト企業admin',
                'email' => 'sorega.dorohedoro+002@gmail.com',
                'password' => '$2y$10$Uce38MwgfgjbTDc66Yvym.3cwvmAVBnnlUqVWJyk4gY63Zrn9E4q.',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        ]);
        // for ($ix = 2; $ix <= 500; $ix++) {
        //     $dispix = sprintf('%03d', $ix);
        //     \DB::table('users')->insert([
        //         [
        //             'id' => $ix,
        //             'name' => $dispix . '太郎',
        //             'email' => $dispix . '@example.com',
        //             'password' => '',
        //             'created_at' => date('Y-m-d H:i:s'),
        //             'updated_at' => date('Y-m-d H:i:s'),
        //         ]
        //     ]);
        // }
    }
}
