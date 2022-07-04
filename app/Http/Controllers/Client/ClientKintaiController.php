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
        $query->select(
            'kintais.*',
            'employees.id AS emp_id',
        );
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

        $kintais = $query->orderBy('day', 'DESC')->orderBy('order', 'ASC')->orderBy('employees.id', 'ASC')->limit(
            config('const.max_get')
        )->get();

        // 勤務時間を計算
        $kintais->map(function ($val) {
            if (
                $val['time_1'] !== NULL
                && $val['time_6'] !== NULL
            ) {
                $st = Carbon::parse($val['time_1']);
                $ed = Carbon::parse($val['time_6']);
                $st->diffInHours($ed);
                //$day = DatetimeUtility::date('Y/m/d H:i:s', $ddd->timestamp);
                //dd($st->diffInHours($ed));
                $minutes = $st->diffInMinutes($ed);
                $val['work_hour'] = floor_reitengo($minutes / 60);
            } else {
                $val['work_hour'] = NULL;
            }
            return $val;
        });

// foreach ($kintais as $key => $kintai) {
// ddd($kintai);
//     # code...
// }
        return view('client/kintai/index', compact('kintais', 'search', 'collapse', 'employees'));
    }

    public function edit($id = null) {

        if ($id == null) {
            $mode = config('const.editmode.create');
            $kintai = New Kintai; //新規なので空のインスタンスを渡す

        } else {
            $mode = config('const.editmode.edit');

            //$report = Report::findOrFail($id);

            $query = Kintai::query();
            $query->where('id', $id);
            $query->where('client_id', Auth::id());
            $kintai = $query->firstOrFail();
        }


        // クライアントIDを元に社員一覧
        $employeeService = New EmployeeService();
        $employees = $employeeService->findEmployeesByClientId(Auth::id());


        // 休憩設定により打刻ループを設定
        if (Auth::user()->rest == 1) {
            $dakoku_names = config('const.dakokunames_rest_1');
        } else if (Auth::user()->rest == 2) {
            $dakoku_names = config('const.dakokunames_rest_2');
        } else if (Auth::user()->rest == 3) {
            $dakoku_names = config('const.dakokunames_rest_3');
        }

        return view('client/kintai/edit', compact('kintai', 'mode', 'employees', 'dakoku_names'));
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
