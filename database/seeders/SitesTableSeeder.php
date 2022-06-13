<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SitesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($ix = 1; $ix <= 100; $ix++) {
            $dispix = sprintf('%03d', $ix);
            \DB::table('sites')->insert([
                [
                    'company_id' => 1,
                    'name' => '元1' . $dispix . '作業所',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]
            ]);
        }

        for ($ix = 1; $ix <= 100; $ix++) {
            $dispix = sprintf('%03d', $ix);
            \DB::table('sites')->insert([
                [
                    'company_id' => 2,
                    'name' => '元2 ' . $dispix . '作業所',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]
            ]);
        }
    }
}
