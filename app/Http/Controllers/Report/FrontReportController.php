<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\ReportWorking;
use App\Models\ReportDriver;
use App\Models\Company;
use App\Models\Site;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class FrontReportController extends Controller
{
    public $search_session_name;
    public $companies;
    public $sites_all;
    public $sites_new;
    public $workers;

    function __construct() {
        $this->search_session_name = 'frontreport';

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

        // 元請け一覧
        $companies = Company::all()->sortBy('id');
        // key,value ペアに直す
        $this->companies = $companies->pluck('name','id')->prepend( "選択してください", "");

        // 作業所一覧
        $sites = Site::all()->sortBy('id');
        // key,value ペアに直す
        $this->sites_all = $sites->pluck(null,'id')->toArray();

        // 作業所一覧 済でない物のみ 新規の時はこっちを使う
        $sites = Site::where('is_done', 0)->orderBy('id')->get();
        // key,value ペアに直す
        $this->sites_new = $sites->pluck(null,'id')->toArray();


        // 作業員一覧
        $workers = Worker::all()->sortBy('kana');
        // key,value ペアに直す
        $this->workers = $workers->pluck('name','id')->prepend( "", "");
    }

    public function index(Request $request) {
        // cardの開閉 全閉じ状態を初期値 必要に応じてオープン
        $collapse = config('const.COLLAPSE.CLOSE');



        $query = Report::query();

        //検索
        $method = $request->method();
        $session = $request->session()->get($this->search_session_name);

        $search = [];
        $search_keys = [
            'day_st',
            'day_ed',
            'company_id',
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

        if ($search['company_id']) {
            $query->where('company_id', $search['company_id']);
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

        $companies = $this->companies;
        $sites = $this->sites_all;

        return view('report/index', compact('reports', 'search', 'collapse', 'companies', 'sites'));
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

            // 済でない作業所のみ
            $sites = $this->sites_new;

        } else {
            $mode = config('const.editmode.edit');
            $report = Report::find($id);

            // 作業所全て
            $sites = $this->sites_all;

            if (!empty($report->koji_1_memo)) $collapse[1] = config('const.COLLAPSE.OPEN');
            if (!empty($report->koji_2_memo)) $collapse[2] = config('const.COLLAPSE.OPEN');
            if (!empty($report->koji_3_memo)) $collapse[3] = config('const.COLLAPSE.OPEN');
            if (!empty($report->koji_4_memo)) $collapse[4] = config('const.COLLAPSE.OPEN');
            if (!empty($report->koji_5_memo)) $collapse[5] = config('const.COLLAPSE.OPEN');

            // 鳶土工 2～5の入力があれば 追加ボタンを開く
            foreach(config('const.KOJINAMES') as $kojikey => $kojiname) {
                foreach(config('const.TOBI_DOKO') as $tobidokokey => $tobidokoname) {
                    if (
                        !empty($report->{"koji_" . $kojikey . "_" . $tobidokokey . "_2_num"})
                        || !empty($report->{"koji_" . $kojikey . "_" . $tobidokokey . "_3_num"})
                        || !empty($report->{"koji_" . $kojikey . "_" . $tobidokokey . "_4_num"})
                        || !empty($report->{"koji_" . $kojikey . "_" . $tobidokokey . "_5_num"})
                    ) {
                        $collapse["{$tobidokokey}{$kojikey}"] = true;
                    }
                }
            }

            // 作業員 15～29人の入力があれば 追加ボタンを開く
            foreach ($report->reportworkings as $key => $reportworking) {
                if ($key >= 15 && $key <= 29) {
                    if (isset($reportworking->worker_id)) {
                        $collapse[98] = true;
                    }
                }
            }
            // 作業員 30人以降の入力があれば 協力員カードは開く
            foreach ($report->reportworkings as $key => $reportworking) {
                if ($key >= 30) {
                    if (isset($reportworking->worker_id)) {
                        $collapse[99] = config('const.COLLAPSE.OPEN');
                    }
                }
            }
        }


        $companies = $this->companies;
        $workers = $this->workers;

        return view('report/edit', compact('report', 'mode', 'collapse', 'companies', 'sites', 'workers'));
    }

    public function update(Request $request, $id = null) {
        $request->validate([
            'day' => 'required|date',
            'company_id' => 'required',
            'site_id' => 'required',
        ]
        ,[
            'day.required' => '必須項目です。',
            'day.date' => '有効な日付を指定してください。',
            'company_id.required' => '必須項目です。',
            'site_id.required' => '必須項目です。',
        ]);



        // 更新対象データ
        $updarr = [
            'day' => $request->input('day'),
            'company_id' => $request->input('company_id'),
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

        // Illegal offset typeでちゃんと動かないのでDELETE INSERTにしようかな
        // https://jpn.itlibra.com/article?id=21121
        // for ($ix = 0; $ix < 60; $ix++) {
        //     ReportWorking::updateOrCreate(
        //         ['report_id' => $result->id, 'no' => $ix],
        //         [
        //             'worker_id' => $request->input("worker_id.{$ix}"),
        //             'tobidoko' => $request->input("tobidoko.{$ix}"),
        //         ],
        //     );
        // }
        $db_data = new ReportWorking;
        $db_data->where('report_id', $result->id)->delete();
        for ($ix = 0; $ix < 60; $ix++) {
            ReportWorking::create(
                [
                    'report_id' => $result->id,
                    'no' => $ix,
                    'worker_id' => $request->input("worker_id.{$ix}"),
                    'tobidoko' => $request->input("tobidoko.{$ix}"),
                    'sozan' => $request->input("sozan.{$ix}"),
                ]
            );
        }

        $db_data = new ReportDriver;
        $db_data->where('report_id', $result->id)->delete();
        for ($ix = 0; $ix < 3; $ix++) {
            ReportDriver::create(
                [
                    'report_id' => $result->id,
                    'no' => $ix,
                    'worker_id' => $request->input("driver_id.{$ix}"),
                ]
            );
        }




        // base64で送られてくるサイン画像を保存
        $base64 = $request->input("sign_base64");

        preg_match('/data:image\/(\w+);base64,/', $base64, $matches);

        $extension = $matches[1];

        $img = preg_replace('/^data:image.*base64,/', '', $base64);
        $img = str_replace(' ', '+', $img);

        $fileData = base64_decode($img);

        $path = $result->id . '.' . $extension;

        Storage::disk("public")->put('sign/' . $path , $fileData);

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


















    // 勤怠入力画面 サンプル用
    public function kintai() {
        config(['adminlte.title' => '']);
        config(['adminlte.logo' => '']);
        return view('kintai');
    }
}
