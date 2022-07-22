<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Kintai;
use Carbon\Carbon;

class KintaiService
{

    /*
        * Kintais IDを渡すとその行の勤務時間を再計算して登録しなおし
    */
    public function calcAndUpdateWorkHour($id)
    {
        $kintai = Kintai::find($id);
        if ($kintai === NULL) return;

        $client = Client::find($kintai->client_id);
        if ($client === NULL) return;



        $kintai->work_hour = 0;
        $kintai->rest_hour = 0;
        if (
            $kintai->time_1 !== NULL
            && $kintai->time_6 !== NULL
        ) {
            $st = Carbon::parse($kintai->time_1);
            $ed = Carbon::parse($kintai->time_6);

            // 深夜残業だったら 01時などと保存されているので +1日した01時として考える
            if ($kintai['midnight']) {
                $ed->addDay(); // 1日後
            }

            $rest_1_hour = 0;
            $rest_2_hour = 0;
            $minutes_1 = 0;
            $minutes_2 = 0;
            if ($client->rest == 2 || $client->rest == 3) {
                if (
                    $kintai->time_2 !== NULL
                    && $kintai->time_3 !== NULL
                ) {
                    $rest_1_st = Carbon::parse($kintai->time_2);
                    $rest_1_ed = Carbon::parse($kintai->time_3);
                    $minutes_1 = $rest_1_st->diffInMinutes($rest_1_ed);
                    $rest_1_hour = reitennigo_ceil($minutes_1 / 60);  // 休憩は0.25ごとに切り上げ
                }
            }
            if ($client->rest == 3) {
                if (
                    $kintai->time_4 !== NULL
                    && $kintai->time_5 !== NULL
                ) {
                    $rest_2_st = Carbon::parse($kintai->time_4);
                    $rest_2_ed = Carbon::parse($kintai->time_5);
                    $minutes_2 = $rest_2_st->diffInMinutes($rest_2_ed);
                    $rest_2_hour = reitennigo_ceil($minutes_2 / 60);  // 休憩は0.25ごとに切り上げ
                }
            }

            $minutes = $st->diffInMinutes($ed);
            $work_hour = reitennigo_floor($minutes / 60);   // 勤務時間は0.25ごとに切り捨て

            $kintai->work_hour = $work_hour - $rest_1_hour - $rest_2_hour;
            $kintai->rest_hour = $rest_1_hour + $rest_2_hour;
            // $kintai['hourfull'] = $work_hour;
            // $kintai['hour1'] = $rest_1_hour;
            // $kintai['hour2'] = $rest_2_hour;
            // $kintai['minute1'] = $minutes_1;
            // $kintai['minute2'] = $minutes_2;
        }

        $kintai->save();
        return $kintai;
    }

}
