<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReportsTableSeeder extends Seeder
{
    /**
    * Run the database seeds.
    *
    * @return void
    */
    public function run()
    {
        $cnt = 0;
        $tons = config('const.JUKI');
        $sozans = config('const.SOZAN');
        $kojikbn = config('const.KOJI.KOJI_KBN_LIST_NAME');
        for ($iday = 1; $iday <= 100; $iday++) {
            $cnt++;

            $data = [];
            $data['id'] = $cnt;
            $data['day'] = date('Y-m-d H:i:s', strtotime('2022/06/05 -' . $iday . ' day'));
            $data['company_id'] = 1;
            $data['site_id'] = 1;

            $data['koji_1_memo'] = '仮設のメモ';
            $data['koji_2_memo'] = 'コンクリートのメモ';
            $data['koji_3_memo'] = '土工のメモ';
            $data['koji_4_memo'] = '常傭①のメモ';
            $data['koji_5_memo'] = '常傭②のメモ';
            $data['koji_2_dasetu'] = 1;

            $data['koji_4_kbn'] = (int)array_rand($kojikbn);
            $data['koji_5_kbn'] = (int)array_rand($kojikbn);

            for ($ix = 1; $ix <= 5; $ix++) {
                for ($iy = 1; $iy <= 5; $iy++) {
                    $data["koji_{$ix}_tobi_{$iy}_sttime"] = '08:00:00';
                    $data["koji_{$ix}_tobi_{$iy}_edtime"] = '17:00:00';
                    $data["koji_{$ix}_tobi_{$iy}_num"] = rand(0,10);
                    $data["koji_{$ix}_tobi_{$iy}_sozan"] = (float)array_rand($sozans);
                    $data["koji_{$ix}_doko_{$iy}_sttime"] = '08:00:00';
                    $data["koji_{$ix}_doko_{$iy}_edtime"] = '17:00:00';
                    $data["koji_{$ix}_doko_{$iy}_num"] = rand(0,10);
                    $data["koji_{$ix}_doko_{$iy}_sozan"] = (float)array_rand($sozans);
                }
            }
            for ($ix = 2; $ix <= 3; $ix++) {
                for ($iy = 1; $iy <= 5; $iy++) {
                    $data["koji_{$ix}_car_{$iy}_sttime"] = '08:00:00';
                    $data["koji_{$ix}_car_{$iy}_edtime"] = '17:00:00';
                    $data["koji_{$ix}_car_{$iy}_ton"] = (int)array_rand($tons);
                    $data["koji_{$ix}_car_{$iy}_num"] = rand(0,10);
                    $data["koji_{$ix}_car_{$iy}_sozan"] = (float)array_rand($sozans);
                }
            }

            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');


            \DB::table('reports')->insert([
                $data
            ]);
        }

    }
}
