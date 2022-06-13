<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\ReportWorking;
use App\Models\ReportDriver;
use App\Models\Company;
use App\Models\Site;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;
use App\Services\DatetimeUtility;

class ReportController extends Controller
{
    public $search_session_name;
    public $companies;
    public $sites;
    public $workers;

    function __construct() {
        $this->search_session_name = 'report';

        // cardの開閉 全閉じ状態を初期値 必要に応じてオープン
        $collapse = config('const.COLLAPSE.CLOSE');

        // 元請け一覧
        $companies = Company::all()->sortBy('id');
        // key,value ペアに直す
        $this->companies = $companies->pluck('name','id')->prepend( "選択してください", "");

        // 作業所一覧
        $sites = Site::all()->sortBy('id');
        // key,value ペアに直す
        $this->sites = $sites->pluck(null,'id')->toArray();

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
        $sites = $this->sites;

        return view('admin/report/index', compact('reports', 'search', 'collapse', 'companies', 'sites'));
    }

    public function view($id = null) {

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
        $sites = $this->sites;
        $workers = $this->workers;

        return view('admin/report/view', compact('report', 'mode', 'collapse', 'companies', 'sites', 'workers'));
    }









    public function output($id) {
        $report = Report::find($id);

        $companies = $this->companies;
        $sites = $this->sites;
        $workers = $this->workers;

        // 日付
        $ddd = Carbon::parse($report->day);
        $day = isset($report->day) ? DatetimeUtility::date('Jk年n月j日', $ddd->timestamp) : '';

        // 作業員情報を詰めて生成
        $workerlists = [];
        $cnt = 0;
        $keynum = 0;
        foreach ($report->reportworkings as $working) {
            if (isset($working->worker_id)) {
                $workerlists[$keynum][] = [
                    'name' => $working->worker->name,
                    'tobidoko' => config('const.TOBI_DOKO_KBN_SHORT.' . $working->tobidoko),
                    'sozan' => $working->sozan,
                ];

                // 6件ごとに配列キー増加
                $cnt++;
                if ($cnt >= 6) {
                    $cnt = 0;
                    $keynum++;
                }
            }
        }

        // 運転者情報を詰めて生成
        $driverlists = [];
        $cnt = 0;
        $keynum = 0;
        foreach ($report->reportdrivers as $driver) {
            if (isset($driver->worker_id)) {
                $driverlists[$keynum][] = ['name' => $driver->worker->name];

                // 6件ごとに配列キー増加
                $cnt++;
                if ($cnt >= 6) {
                    $cnt = 0;
                    $keynum++;
                }
            }
        }


        $pdf = \PDF::loadView('admin.report.pdf', compact('report', 'companies', 'sites', 'workers', 'day', 'workerlists', 'driverlists'));
        $pdf->setPaper('A4');  // 縦向き
        //$pdf->setPaper('A4', 'landscape');  // 横向き
        //return $pdf->stream();
        return $pdf->download('作業証明書_' . $report->company->name . '_' . $report->site->name . '_' . $report->day->format('ymd') . '.pdf');
    }
}
