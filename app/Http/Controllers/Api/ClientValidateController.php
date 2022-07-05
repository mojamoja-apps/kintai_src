<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kintai;
use Illuminate\Http\Request;

class ClientValidateController extends Controller
{
    // 勤怠打刻の重複チェック
    // クライアントID、社員ID、日付、勤怠ID(編集か新規か)
    public function kintai(Request $request) {

        $query = Kintai::query();

        if ($request->input('day')) {
            $query->whereDate('day', '=', $request->input('day'));
        }

        if ($request->input('client_id')) {
            $query->where('client_id', $request->input('client_id'));
        }

        if ($request->input('employee_id')) {
            $query->where('employee_id', $request->input('employee_id'));
        }

        if ($request->input('id')) {
            $query->where('id', '<>', $request->input('id'));
        }

        $kintai = $query->first();

        if ($kintai === null) {
            // 重複なし＝登録OK
            return 'true';
        } else {
            // 重複有り＝登録NG
            return 'false';
        }

    }
}
