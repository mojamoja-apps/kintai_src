<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReportWorkingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($ix = 1; $ix <= 100; $ix++) {
            for ($iy = 0; $iy < 35; $iy++) {
                \DB::table('report_workings')->insert([
                    [
                        'report_id' => $ix,
                        'no' => $iy,
                        'worker_id' => rand(1,4),
                        'tobidoko' => rand(1,2),
                        'sozan' => (float)array_rand(config('const.SOZAN')),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]
                ]);
            }
        }
    }
}
