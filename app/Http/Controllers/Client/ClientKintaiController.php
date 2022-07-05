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

        if ($search['keyword']) {
            // 全角スペースを半角に変換
            $spaceConversion = mb_convert_kana($search['keyword'], 's');
            // 単語を半角スペースで区切り、配列にする（例："山田 翔" → ["山田", "翔"]）
            $wordArraySearched = preg_split('/[\s,]+/', $spaceConversion, -1, PREG_SPLIT_NO_EMPTY);
            // 単語をループで回し、ユーザーネームと部分一致するものがあれば、$queryとして保持される
            foreach($wordArraySearched as $value) {
                $query->where('employees.name', 'like', '%'.$value.'%')
                    ->OrWhere('employees.code', 'like', '%'.$value.'%')
                    ->OrWhere('memo_1', 'like', '%'.$value.'%')
                    ->OrWhere('memo_2', 'like', '%'.$value.'%')
                    ->OrWhere('memo_3', 'like', '%'.$value.'%')
                    ->OrWhere('memo_4', 'like', '%'.$value.'%')
                    ->OrWhere('memo_5', 'like', '%'.$value.'%')
                    ->OrWhere('memo_6', 'like', '%'.$value.'%')
                ;
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

            //$kintai = Kintai::findOrFail($id);

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




    // 更新処理
    public function update(Request $request, $id = null) {
        $request->validate([
            'day' => 'required|date',
            'employee_id' => 'required',
        ]
        ,[
            'day.required' => '必須項目です。',
            'day.date' => '有効な日付を指定してください。',
            'employee_id.required' => '必須項目です。',
        ]);

        // 更新対象データ
        $time_1 = NULL;
        if ($request->input('time_1') != '') {
            $time_1 = substr($request->input('time_1'), 0, 2) . ':' . substr($request->input('time_1'), 2, 2);
        }
        $time_2 = NULL;
        if ($request->input('time_2') != '') {
            $time_2 = substr($request->input('time_2'), 0, 2) . ':' . substr($request->input('time_2'), 2, 2);
        }
        $time_3 = NULL;
        if ($request->input('time_3') != '') {
            $time_3 = substr($request->input('time_3'), 0, 2) . ':' . substr($request->input('time_3'), 2, 2);
        }
        $time_4 = NULL;
        if ($request->input('time_4') != '') {
            $time_4 = substr($request->input('time_4'), 0, 2) . ':' . substr($request->input('time_4'), 2, 2);
        }
        $time_5 = NULL;
        if ($request->input('time_5') != '') {
            $time_5 = substr($request->input('time_5'), 0, 2) . ':' . substr($request->input('time_5'), 2, 2);
        }
        $time_6 = NULL;
        if ($request->input('time_6') != '') {
            $time_6 = substr($request->input('time_6'), 0, 2) . ':' . substr($request->input('time_6'), 2, 2);
        }


        $updarr = [
            'day' => $request->input('day'),
            'client_id' => Auth::id(),
            'employee_id' => $request->input('employee_id'),
            'time_1' => $time_1,
            'time_2' => $time_2,
            'time_3' => $time_3,
            'time_4' => $time_4,
            'time_5' => $time_5,
            'time_6' => $time_6,
            'memo_1' => $request->input('memo_1'),
            'memo_2' => $request->input('memo_2'),
            'memo_3' => $request->input('memo_3'),
            'memo_4' => $request->input('memo_4'),
            'memo_5' => $request->input('memo_5'),
            'memo_6' => $request->input('memo_6'),
            'lat_1' => $request->input('lat_1'),
            'lat_2' => $request->input('lat_2'),
            'lat_3' => $request->input('lat_3'),
            'lat_4' => $request->input('lat_4'),
            'lat_5' => $request->input('lat_5'),
            'lat_6' => $request->input('lat_6'),
            'lon_1' => $request->input('lon_1'),
            'lon_2' => $request->input('lon_2'),
            'lon_3' => $request->input('lon_3'),
            'lon_4' => $request->input('lon_4'),
            'lon_5' => $request->input('lon_5'),
            'lon_6' => $request->input('lon_6'),
        ];

        Kintai::updateOrCreate(
            ['id' => $id],
            $updarr,
        );

        // CSRFトークンを再生成して、二重送信対策
        $request->session()->regenerateToken();

        return redirect( route('client.kintai.index') );
    }



}
