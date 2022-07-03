<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Kintai;
use App\Services\ClientService;
use App\Services\EmployeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Auth;

use Carbon\Carbon;
use App\Services\DatetimeUtility;

class ClientKintaiController extends Controller
{
    public $search_session_name;
    public $employees;

    function __construct() {
        $this->search_session_name = 'admin_kintai';

        // cardの開閉 全閉じ状態を初期値 必要に応じてオープン
        $collapse = config('const.COLLAPSE.CLOSE');
    }

    public function index(Request $request) {
        // cardの開閉 全閉じ状態を初期値 必要に応じてオープン
        $collapse = config('const.COLLAPSE.CLOSE');

        // クライアントIDを元に社員一覧
        $employeeService = New EmployeeService();
        $employees = $employeeService->findEmployeesByClientId(Auth::id());





        // 勤怠情報を取得
        $query = Kintai::query();
        $query->where('kintais.client_id', Auth::id());

        // 社員の並び順にしたいので社員マスタをjoin
        $query->leftJoin('employees', 'kintais.employee_id', '=', 'employees.id');

        //検索
        $method = $request->method();
        $session = $request->session()->get($this->search_session_name);

        $search = [];
        $search_keys = [
            'day_st',
            'day_ed',
            'employee_id',
            'is_dakokumore',
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

        if ($search['employee_id']) {
            $query->where('employee_id', $search['employee_id']);
            $open = true;
        }

        if ($search['is_dakokumore']) {
            if (Auth::user()->rest == 1) {
                $query->where(function($query){
                    $query->where('time_1', '=', NULL)
                        ->orWhere('time_6', '=', NULL)
                    ;
                });
            } else if (Auth::user()->rest == 2) {
                $query->where(function($query){
                    $query->where('time_1', '=', NULL)
                        ->orWhere('time_2', '=', NULL)
                        ->orWhere('time_3', '=', NULL)
                        ->orWhere('time_6', '=', NULL)
                    ;
                });
            } else if (Auth::user()->rest == 3) {
                $query->where(function($query){
                    $query->where('time_1', '=', NULL)
                        ->orWhere('time_2', '=', NULL)
                        ->orWhere('time_3', '=', NULL)
                        ->orWhere('time_4', '=', NULL)
                        ->orWhere('time_5', '=', NULL)
                        ->orWhere('time_6', '=', NULL)
                    ;
                });
            }
            $open = true;
        }

        if ($open) {
            $collapse = config('const.COLLAPSE.OPEN');
        }

        $kintais = $query->orderBy('day', 'asc')->orderBy('order', 'asc')->orderBy('employees.id', 'asc')->limit(
            config('const.max_get')
        )->get();


        return view('client/kintai/index', compact('kintais', 'search', 'collapse', 'employees'));
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
