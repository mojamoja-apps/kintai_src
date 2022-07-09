<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KintaisTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($ix = 1; $ix <= 5; $ix++) {
            for ($iday = 1; $iday <= 500; $iday++) {
                \DB::table('kintais')->insert([
                    [
                        'day' => date('Y-m-d H:i:s', strtotime('2022/06/30 -' . $iday . ' day')),
                        'client_id' => 1,
                        'employee_id' => $ix,
                        'time_1' => '09:00',
                        'time_2' => '11:00',
                        'time_3' => '11:30',
                        'time_4' => '15:00',
                        'time_5' => '15:30',
                        'time_6' => '18:00',
                        'work_hour' => '8',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]
                ]);
            }
        }
    }
}
