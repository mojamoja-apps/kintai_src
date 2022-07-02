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
    public $employees;

    function __construct() {
        $this->search_session_name = 'frontkintai';

        // 作業証明書入力画面では 管理画面レイアウトを調整する
        config(['adminlte.title' => '作業証明書入力画面']);
        config(['adminlte.logo' => '作業証明書入力画面']);
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


        // 作業員一覧
        $employees = Employee::all()->sortBy('kana');
        // key,value ペアに直す
        $this->employees = $employees->pluck('name','id')->prepend( "", "");
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
        $time = $dt->format('h:i:s');

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


        $target_column = 'time_' . $request->input('dakokumode'); // 1～6

        // 更新対象データ
        $midnight = 0;
        if (
            $request->input('dakokumode') == config('const.dakokumode.taikin')
            && $request->input('midnight') === 'true'
        ) {
            // ajaxの真偽値は文字列として'true' 'false' が来るので注意して判定
            $midnight = 1;
        }

        $updarr = [
            'client_id' => $client->id,
            'employee_id' => $request->input('employee_id'),
            'day' => $day,
            $target_column => $time,
            'midnight' => $midnight,
            'lat' => $request->input('lat'),
            'lon' => $request->input('lon'),
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
