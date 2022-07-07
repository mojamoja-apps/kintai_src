<?php

namespace App\Http\Controllers\Kintai;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Site;
use App\Models\Employee;
use App\Models\Kintai;
use App\Models\Kintai_rireki;
use App\Services\ClientService;
use App\Services\EmployeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;
use App\Services\DatetimeUtility;

class FrontKintaiController extends Controller
{
    public $search_session_name;
    public $clients;

    function __construct() {
        $this->search_session_name = 'frontkintai';

        // フロントでは 管理画面レイアウトを調整する
        config(['adminlte.title' => '']);
        config(['adminlte.logo' => '']);
        // ユーザーメニュー非表示
        config(['adminlte.usermenu_enabled' => false]);
        // ログアウトメニュー非表示
        config(['adminlte.logout_menu' => false]);
        // トップナビレイアウト
        config(['adminlte.layout_topnav' => true]);
        // ヘッダーメニューなし
        config(['adminlte.no_header_menu' => true]);
        // メニューを削除
        config(['adminlte.menu' => [] ]);

        // 企業一覧
        $clients = Client::all()->sortBy('id');
        // key,value ペアに直す
        $this->clients = $clients->pluck('name','id')->prepend( "選択してください", "");
    }

    public function index(Request $request, $hash) {

        // ハッシュを元にクライアントを検索
        $clientService = New ClientService();
        $client = $clientService->findClientByHash($hash);
        if ($client == null) {
            return \App::abort(404);
        }

        // basic認証
        fn_basic_auth(array($client->basic_user => $client->basic_pass));

        // クライアントIDを元に社員一覧
        $employeeService = New EmployeeService();
        $employees = $employeeService->findEmployeesByClientId($client->id);

        // 社員マスタ取得
        config(['adminlte.title' => '']);
        config(['adminlte.logo' => '']);
        return view('kintai.index', compact('client', 'employees'));

    }


    public function dakoku(Request $request, $hash) {

        // ハッシュを元にクライアントを検索
        $clientService = New ClientService();
        $client = $clientService->findClientByHash($hash);
        if ($client == null) {
            return \App::abort(404);
        }

        $request->validate([
            'employee_id' => 'required',
            'dakokumode' => 'required',
        ]
        ,[
            'employee_id.required' => '打刻ユーザーは必須項目です。',
            'dakokumode.required' => '打刻モードは必須項目です。',
        ]);

        // 既存レコード存在チェック
        $dt = Carbon::now();
        $day = $dt->format('Y/m/d');
        $time = $dt->format('H:i:00');  // 今がなんであれ、打刻は0秒とすること

        // 前日分の退勤として打刻する
        $midnight = 0;
        if (
            $request->input('dakokumode') == config('const.dakokumode.taikin')
            && $request->input('midnight') === 'true'
        ) {
            // ajaxの真偽値は文字列として'true' 'false' が来るので注意して判定

            // 日付は前日分にする
            // 日付はそのまま
            // 深夜フラグを立てておく
            $dt = new Carbon('yesterday'); // 昨日
            $day = $dt->format('Y/m/d');

            $midnight = 1;
        }




        $kintai = Kintai::
                    where('client_id', $client->id)
                    ->where('employee_id', $request->input('employee_id'))
                    ->where('day', $day)
                    ->first();

        if ($kintai === null) {
            // 新規レコード
            $id = null;
        } else {
            // 更新
            $id = $kintai->id;
        }


         // 対象カラム モードにより1～6
        $time_column = 'time_' . $request->input('dakokumode');
        $lat_column = 'lat_' . $request->input('dakokumode');
        $lon_column = 'lon_' . $request->input('dakokumode');
        $memo_column = 'memo_' . $request->input('dakokumode');

        // 更新対象データ



        $updarr = [
            'client_id' => $client->id,
            'employee_id' => $request->input('employee_id'),
            'day' => $day,
            $time_column => $time,
            'midnight' => $midnight,
            $lat_column => $request->input('lat'),
            $lon_column => $request->input('lon'),
            $memo_column => $request->input('memo'),
        ];

        $result = Kintai::updateOrCreate(
            ['id' => $id],
            $updarr,
        );


        //打刻履歴テーブルも作る
        $updarr = [
            'client_id' => $client->id,
            'employee_id' => $request->input('employee_id'),
            'day' => $day,
            'time' => $time,
            'midnight' => $midnight,
            'lat' => $request->input('lat'),
            'lon' => $request->input('lon'),
            'memo' => $request->input('memo'),
        ];
        $rireki = Kintai_rireki::create($updarr);

        // CSRFトークンを再生成して、二重送信対策
        $request->session()->regenerateToken();

        return response()->json(['result' => true, 'error_code' => '0', 'error_message' => '']);
    }

    public function destroy(Request $request, $id) {
        $report = Report::find($id);
        $report->delete();

        // CSRFトークンを再生成して、二重送信対策
        $request->session()->regenerateToken();

        return redirect( route('report.index') );
    }

















}
