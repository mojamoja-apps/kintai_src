<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('users')->insert([
            [
                'id' => 1,
                'name' => 'admin',
                'email' => 'sorega.dorohedoro@gmail.com',
                'password' => '$2y$10$Uce38MwgfgjbTDc66Yvym.3cwvmAVBnnlUqVWJyk4gY63Zrn9E4q.',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        ]);
        for ($ix = 2; $ix <= 500; $ix++) {
            $dispix = sprintf('%03d', $ix);
            \DB::table('users')->insert([
                [
                    'id' => $ix,
                    'name' => $dispix . '太郎',
                    'email' => $dispix . '@example.com',
                    'password' => '',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]
            ]);
        }
    }
}
