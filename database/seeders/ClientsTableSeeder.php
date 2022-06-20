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
                'id' => 1,
                'is_enabled' => true,
                'name' => 'テスト企業001',
                'email' => 'sorega.dorohedoro+client001@gmail.com',
                'password' => '$2y$10$Uce38MwgfgjbTDc66Yvym.3cwvmAVBnnlUqVWJyk4gY63Zrn9E4q.',
                'hash' => '123456789abc',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
            ,[
                'id' => 2,
                'is_enabled' => false,
                'name' => '無効企業002',
                'email' => 'sorega.dorohedoro+client002@gmail.com',
                'password' => '$2y$10$Uce38MwgfgjbTDc66Yvym.3cwvmAVBnnlUqVWJyk4gY63Zrn9E4q.',
                'hash' => 'XXX123456789abcXXX',
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
