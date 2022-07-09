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
                    $rest_1_hour = reitengo_ceil($minutes_1 / 60);  // 休憩は0.5ごとに切り上げ
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
                    $rest_2_hour = reitengo_ceil($minutes_2 / 60);  // 休憩は0.5ごとに切り上げ
                }
            }

            $minutes = $st->diffInMinutes($ed);
            $work_hour = reitengo_floor($minutes / 60);   // 勤務時間は0.5ごとに切り捨て

            $kintai->work_hour = $work_hour - $rest_1_hour - $rest_2_hour;
            // $kintai['hourfull'] = $work_hour;
            // $kintai['hour1'] = $rest_1_hour;
            // $kintai['hour2'] = $rest_2_hour;
            // $kintai['minute1'] = $minutes_1;
            // $kintai['minute2'] = $minutes_2;
        }

        $kintai->save();
        return $kintai;
    }

    /*
        * Kintaisテーブルから勤務時間を計算する
    */
/*
    public function calcWorkHour($row, $cient_rest_flg)
    {
        if (
            $row['time_1'] !== NULL
            && $row['time_6'] !== NULL
        ) {
            $st = Carbon::parse($row['time_1']);
            $ed = Carbon::parse($row['time_6']);

            // 深夜残業だったら 01時などと保存されているので +1日した01時として考える
            if ($row['midnight']) {
                $ed->addDay(); // 1日後
            }

            $rest_1_hour = 0;
            $rest_2_hour = 0;
            $minutes_1 = 0;
            $minutes_2 = 0;
            if ($cient_rest_flg == 2 || $cient_rest_flg == 3) {
                if (
                    $row['time_2'] !== NULL
                    && $row['time_3'] !== NULL
                ) {
                    $rest_1_st = Carbon::parse($row['time_2']);
                    $rest_1_ed = Carbon::parse($row['time_3']);
                    $minutes_1 = $rest_1_st->diffInMinutes($rest_1_ed);
                    $rest_1_hour = reitengo_ceil($minutes_1 / 60);  // 休憩は0.5ごとに切り上げ
                }
            }
            if ($cient_rest_flg == 3) {
                if (
                    $row['time_4'] !== NULL
                    && $row['time_5'] !== NULL
                ) {
                    $rest_2_st = Carbon::parse($row['time_4']);
                    $rest_2_ed = Carbon::parse($row['time_5']);
                    $minutes_2 = $rest_2_st->diffInMinutes($rest_2_ed);
                    $rest_2_hour = reitengo_ceil($minutes_2 / 60);  // 休憩は0.5ごとに切り上げ
                }
            }

            $minutes = $st->diffInMinutes($ed);
            $work_hour = reitengo_floor($minutes / 60);   // 勤務時間は0.5ごとに切り捨て

            $row['work_hour'] = $work_hour - $rest_1_hour - $rest_2_hour;
            $row['hourfull'] = $work_hour;
            $row['hour1'] = $rest_1_hour;
            $row['hour2'] = $rest_2_hour;
            $row['minute1'] = $minutes_1;
            $row['minute2'] = $minutes_2;
        } else {
            $row['work_hour'] = NULL;
        }
        return $row;
    }
*/
}
