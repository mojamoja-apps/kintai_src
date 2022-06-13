<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReportDriversTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($ix = 1; $ix <= 300; $ix++) {
            for ($iy = 0; $iy < 3; $iy++) {
                \DB::table('report_drivers')->insert([
                    [
                        'report_id' => $ix,
                        'no' => $iy,
                        'worker_id' => rand(5,7),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]
                ]);
            }
        }
    }
}
