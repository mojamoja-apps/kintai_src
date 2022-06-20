<?php

namespace App\Http\Controllers\Kintai;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Site;
use App\Models\Employee;
use App\Services\ClientService;
use App\Services\EmployeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


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
//ddd($client);

        // basic認証
        fn_basic_auth(array($client->basic_user => $client->basic_pass));

        // クライアントIDを元に社員一覧
        $employeeService = New EmployeeService();
        $employees = $employeeService->findEmployeesByClientId($client->id);
//ddd($employees);
        // 社員マスタ取得
        config(['adminlte.title' => '']);
        config(['adminlte.logo' => '']);
        return view('kintai.index', compact('client', 'employees'));
///////////////////////////////////////////////////////////

        $query = Report::query();

        //検索
        $method = $request->method();
        $session = $request->session()->get($this->search_session_name);

        $search = [];
        $search_keys = [
            'day_st',
            'day_ed',
            'client_id',
            'site_id',
            'keyword',
        ];
        foreach ($search_keys as $keyname) {
            $search[$keyname] = '';
            if($method == "POST"){
                $search[$keyname] = $request->input($keyname) ? $request->input($keyname) : '';
            } else if($method == "GET") {
                $search[$keyname] = isset($session[$keyname]) ? $session[$keyname] : '';
            }
        }

        // セッションを一旦消して検索値を保存
        $request->session()->forget($this->search_session_name);
        $puts = [];
        foreach ($search_keys as $keyname) {
            $puts[$keyname] = $search[$keyname];
        }
        $request->session()->put($this->search_session_name, $puts);


        $open = false; // 検索ボックスを開くか

        if ($search['day_st']) {
            $query->whereDate('day', '>=', $search['day_st']);
            $open = true;
        }

        if ($search['day_ed']) {
            $query->whereDate('day', '<=', $search['day_ed']);
            $open = true;
        }

        if ($search['client_id']) {
            $query->where('client_id', $search['client_id']);
            $open = true;
        }

        if ($search['site_id']) {
            $query->where('site_id', $search['site_id']);
            $open = true;
        }

        if ($search['keyword']) {
            // 全角スペースを半角に変換
            $spaceConversion = mb_convert_kana($search['keyword'], 's');
            // 単語を半角スペースで区切り、配列にする（例："山田 翔" → ["山田", "翔"]）
            $wordArraySearched = preg_split('/[\s,]+/', $spaceConversion, -1, PREG_SPLIT_NO_EMPTY);
            // 単語をループで回し、ユーザーネームと部分一致するものがあれば、$queryとして保持される
            foreach($wordArraySearched as $value) {
                $query->where('koji_1_memo', 'like', '%'.$value.'%')
                    ->OrWhere('koji_2_memo', 'like', '%'.$value.'%')
                    ->OrWhere('koji_3_memo', 'like', '%'.$value.'%')
                    ->OrWhere('koji_4_memo', 'like', '%'.$value.'%')
                    ->OrWhere('koji_5_memo', 'like', '%'.$value.'%')
                ;
            }
            $open = true;
        }

        if ($open) {
            $collapse = config('const.COLLAPSE.OPEN');
        }

        $reports = $query->orderBy('day', 'desc')->orderBy('id', 'asc')->limit(
            config('const.max_get')
        )->get();

        $clients = $this->clients;

        return view('report/index', compact('reports', 'search', 'collapse', 'clients'));
    }

    public function edit($id = null) {

        // cardの開閉 全閉じ状態を初期値 必要に応じてオープン
        $collapse = [];
        $collapse[1] = config('const.COLLAPSE.CLOSE');
        $collapse[2] = config('const.COLLAPSE.CLOSE');
        $collapse[3] = config('const.COLLAPSE.CLOSE');
        $collapse[4] = config('const.COLLAPSE.CLOSE');
        $collapse[5] = config('const.COLLAPSE.CLOSE');
        $collapse[98] = false; // 作業員追加ボタン
        $collapse[99] = config('const.COLLAPSE.CLOSE'); // 99:協力員

        $collapse['tobi1'] = false;
        $collapse['tobi2'] = false;
        $collapse['tobi3'] = false;
        $collapse['tobi4'] = false;
        $collapse['tobi5'] = false;
        $collapse['doko1'] = false;
        $collapse['doko2'] = false;
        $collapse['doko3'] = false;
        $collapse['doko4'] = false;
        $collapse['doko5'] = false;

        if ($id == null) {
            $mode = config('const.editmode.create');
            $report = New Report; //新規なので空のインスタンスを渡す

        } else {
            $mode = config('const.editmode.edit');
            $report = Report::find($id);

        }


        $clients = $this->clients;
        $employees = $this->employees;

        return view('report/edit', compact('report', 'mode', 'collapse', 'clients', 'employees'));
    }

    public function update(Request $request, $id = null) {
        $request->validate([
            'day' => 'required|date',
            'client_id' => 'required',
            'site_id' => 'required',
        ]
        ,[
            'day.required' => '必須項目です。',
            'day.date' => '有効な日付を指定してください。',
            'client_id.required' => '必須項目です。',
            'site_id.required' => '必須項目です。',
        ]);



        // 更新対象データ
        $updarr = [
            'day' => $request->input('day'),
            'client_id' => $request->input('client_id'),
            'site_id' => $request->input('site_id'),
        ];
        for ($ix = 1; $ix <= 5; $ix++) {
            $updarr["koji_{$ix}_kbn"] = $request->input("koji_{$ix}_kbn");
            $updarr["koji_{$ix}_memo"] = $request->input("koji_{$ix}_memo");
            for ($iy = 1; $iy <= 5; $iy++) {
                $updarr["koji_{$ix}_tobi_{$iy}_sttime"] = $request->input("koji_{$ix}_tobi_{$iy}_sttime");
                $updarr["koji_{$ix}_tobi_{$iy}_edtime"] = $request->input("koji_{$ix}_tobi_{$iy}_edtime");
                $updarr["koji_{$ix}_tobi_{$iy}_num"] = $request->input("koji_{$ix}_tobi_{$iy}_num");
                $updarr["koji_{$ix}_tobi_{$iy}_sozan"] = $request->input("koji_{$ix}_tobi_{$iy}_sozan");
                $updarr["koji_{$ix}_doko_{$iy}_sttime"] = $request->input("koji_{$ix}_doko_{$iy}_sttime");
                $updarr["koji_{$ix}_doko_{$iy}_edtime"] = $request->input("koji_{$ix}_doko_{$iy}_edtime");
                $updarr["koji_{$ix}_doko_{$iy}_num"] = $request->input("koji_{$ix}_doko_{$iy}_num");
                $updarr["koji_{$ix}_doko_{$iy}_sozan"] = $request->input("koji_{$ix}_doko_{$iy}_sozan");
            }
            $updarr["koji_{$ix}_dasetu"] = $request->input("koji_{$ix}_dasetu");
            for ($iy = 1; $iy <= 5; $iy++) {
                $updarr["koji_{$ix}_car_{$iy}_sttime"] = $request->input("koji_{$ix}_car_{$iy}_sttime");
                $updarr["koji_{$ix}_car_{$iy}_edtime"] = $request->input("koji_{$ix}_car_{$iy}_edtime");
                $updarr["koji_{$ix}_car_{$iy}_ton"] = $request->input("koji_{$ix}_car_{$iy}_ton");
                $updarr["koji_{$ix}_car_{$iy}_num"] = $request->input("koji_{$ix}_car_{$iy}_num");
                $updarr["koji_{$ix}_car_{$iy}_sozan"] = $request->input("koji_{$ix}_car_{$iy}_sozan");
            }
        }

        $result = Report::updateOrCreate(
            ['id' => $id],
            $updarr,
        );

        // CSRFトークンを再生成して、二重送信対策
        $request->session()->regenerateToken();

        return redirect( route('report.index') );
    }

    public function destroy(Request $request, $id) {
        $report = Report::find($id);
        $report->delete();

        // 添付ファイル削除
        $path = $id . '.png';
        Storage::disk("public")->delete('sign/' . $path);

        // CSRFトークンを再生成して、二重送信対策
        $request->session()->regenerateToken();

        return redirect( route('report.index') );
    }

















}
