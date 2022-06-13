<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;

class ValidateController extends Controller
{
    // 作業証明書の重複チェック
    // 元請けID、作業所ID、日付、作業証明書ID(編集か新規か)
    public function report(Request $request) {

        $query = Report::query();

        if ($request->input('day')) {
            $query->whereDate('day', '=', $request->input('day'));
        }

        if ($request->input('company_id')) {
            $query->where('company_id', $request->input('company_id'));
        }

        if ($request->input('site_id')) {
            $query->where('site_id', $request->input('site_id'));
        }

        if ($request->input('id')) {
            $query->where('id', '<>', $request->input('id'));
        }

        $reports = $query->first();

        if ($reports === null) {
            // 重複なし＝登録OK
            return 'true';
        } else {
            // 重複有り＝登録NG
            return 'false';
        }

    }
}
