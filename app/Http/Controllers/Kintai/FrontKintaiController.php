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


    public function dakoku(Request $request) {

        // ハッシュを元にクライアントを検索
        $clientService = New ClientService();
        $client = $clientService->findClientByHash($hash);
        if ($client == null) {
            return \App::abort(404);
        }

        // 更新対象データ
        $updarr = [
            'client_id' => $client->id,
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
