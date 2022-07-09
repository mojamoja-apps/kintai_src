<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Kintai;
use App\Services\ClientService;
use App\Services\EmployeeService;
use App\Services\KintaiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Auth;

use Carbon\Carbon;
use App\Services\DatetimeUtility;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\File;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ClientKintaiController extends Controller
{
    public $search_session_name;
    public $employees;

    function __construct() {
        $this->search_session_name = 'client_kintai';

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

//   foreach ($kintais as $key => $kintai) {
//   dd($kintai);
//       # code...
//   }
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

        $result = Kintai::updateOrCreate(
            ['id' => $id],
            $updarr,
        );


        // 勤務時間を再計算
        $kintaiService = New KintaiService();
        $kintaiService->calcAndUpdateWorkHour($result->id);

        // CSRFトークンを再生成して、二重送信対策
        $request->session()->regenerateToken();

        return redirect( route('client.kintai.index') );
    }




    public function destroy(Request $request, $id) {

        $query = Kintai::query();
        $query->where('id', $id);
        $query->where('client_id', Auth::id());
        $select = $query->firstOrFail();

        $kintai = Kintai::find($select->id);
        $kintai->delete();


        // CSRFトークンを再生成して、二重送信対策
        $request->session()->regenerateToken();

        return redirect( route('client.kintai.index') );
    }










    // ダウンロード メニュー
    public function dlindex() {
        // 西暦 今年～2022年まで
        $years = [];
        for ($ix = date('Y'); $ix >= 2022; $ix--) {
            $years[$ix] = $ix;
        }
        $months = [];
        for ($ix = 1; $ix <= 12; $ix++) {
            $months[$ix] = $ix;
        }

        // セッションの値を全て取得
        $session = session()->all();
        $session = $session['kintai'] ?? [];

        return view('client/kintai/dlindex', compact('years', 'months', 'session'));
    }




    // Excel出力
    public function excel(Request $request) {
        $request->validate([
            'year' => 'required',
            'month' => 'required',
        ]
        ,[
            'year.required' => '必須項目です。',
            'month.required' => '必須項目です。',
        ]);

        $year = $request->input('year');
        $month = $request->input('month');

        $dt = new Carbon("{$year}/{$month}/01");
        $start = $dt->startOfMonth()->toDateString();
        $dt = new Carbon("{$year}/{$month}/01");
        $end = $dt->endOfMonth()->toDateString();


        // $workers = Worker::where('style', '=', '1')->orderBy('id', 'asc')->get();  // 1:正社員
        // $parts = Worker::where('style', '=', '2')->orderBy('id', 'asc')->get();  // 2:実習生

        // セッションを一旦消して検索値を保存
        $session = $request->session()->get('kintai');
        $search = [];
        $search['year'] = $request->input('year');
        $search['month'] = $request->input('month');
        $request->session()->forget('kintai');
        $request->session()->put('kintai',$search);


        // Excel用意
        $spreadsheet = IOFactory::load(resource_path() . '/excel/kintai_template.xlsx');
        // 作業シート
        $sheet = $spreadsheet->getActiveSheet();



        // 勤怠情報を取得
        $query = Kintai::query();
        $query->select(
            'kintais.*',
            'employees.id AS emp_id',
        );
        $query->where('kintais.client_id', Auth::id());

        // 社員の並び順にしたいので社員マスタをjoin
        $query->leftJoin('employees', 'kintais.employee_id', '=', 'employees.id');

        $query->whereDate('day', '>=', $start);
        $query->whereDate('day', '<=', $end);

        $kintais = $query->orderBy('day', 'ASC')->orderBy('order', 'ASC')->orderBy('employees.id', 'ASC')->get();


        if (Auth::user()->rest == 1) {
            $dakoku_names = config('const.dakokunames_rest_1');
        } else if (Auth::user()->rest == 2) {
            $dakoku_names = config('const.dakokunames_rest_2');
        } else if (Auth::user()->rest == 3) {
            $dakoku_names = config('const.dakokunames_rest_3');
        }
        foreach ($dakoku_names as $value) {
            $head[] = $value;
        }

        // Excel2行目からスタート
        $row = 2;
        foreach ($kintais as $kintai) {

            $sheet->setCellValue("A{$row}", $kintai->day->format('Y/m/d'));
            $sheet->setCellValue("B{$row}", $kintai->employee->code);
            $sheet->setCellValue("C{$row}", $kintai->employee->name);
            $sheet->setCellValue("D{$row}", $kintai->work_hour);
            $sheet->setCellValue("E{$row}", $kintai->time_1 !== null ? $kintai->time_1->format('H:i') : '');
            $sheet->setCellValue("F{$row}", $kintai->time_2 !== null ? $kintai->time_2->format('H:i') : '');
            $sheet->setCellValue("G{$row}", $kintai->time_3 !== null ? $kintai->time_3->format('H:i') : '');
            $sheet->setCellValue("H{$row}", $kintai->time_4 !== null ? $kintai->time_4->format('H:i') : '');
            $sheet->setCellValue("I{$row}", $kintai->time_5 !== null ? $kintai->time_5->format('H:i') : '');
            $sheet->setCellValue("J{$row}", $kintai->time_6 !== null ? $kintai->time_6->format('H:i') : '');
            // if (Auth::user()->rest == 1) {
            //     $arr[] = $kintai->time_1;
            //     $arr[] = $kintai->time_6;
            // } else if (Auth::user()->rest == 2) {
            //     $arr[] = $kintai->time_1;
            //     $arr[] = $kintai->time_2;
            //     $arr[] = $kintai->time_3;
            //     $arr[] = $kintai->time_6;
            // } else if (Auth::user()->rest == 3) {
            //     $arr[] = $kintai->time_1;
            //     $arr[] = $kintai->time_2;
            //     $arr[] = $kintai->time_3;
            //     $arr[] = $kintai->time_4;
            //     $arr[] = $kintai->time_5;
            //     $arr[] = $kintai->time_6;
            // }
            $row++;
        }

        File::setUseUploadTempDirectory(resource_path());

        $tmpfile = '/excel/kintai' . Auth::id() . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save(resource_path() . $tmpfile);

        // ダウンロード完了でリダイレクト用クッキー
        setcookie("downloaded", 1, time()+5);

        return response()->download(resource_path() . $tmpfile, 'kintai_' . $year . sprintf('%02d', $month) . '.xlsx',
                               ['content-type' => 'application/vnd.ms-excel',])
                         ->deleteFileAfterSend(true);
    }



    // スマイル形式csv
    public function smilecsv(Request $request) {
        $request->validate([
            'year' => 'required',
            'month' => 'required',
        ]
        ,[
            'year.required' => '必須項目です。',
            'month.required' => '必須項目です。',
        ]);

        $year = $request->input('year');
        $month = $request->input('month');

        // 月初・月末
        $dt = new Carbon("{$year}/{$month}/01");
        $start = $dt->startOfMonth()->toDateString();
        $dt = new Carbon("{$year}/{$month}/01");
        $end = $dt->endOfMonth()->toDateString();

        // セッションを一旦消して検索値を保存
        $session = $request->session()->get('kintai');
        $search = [];
        $search['year'] = $request->input('year');
        $search['month'] = $request->input('month');
        $request->session()->forget('kintai');
        $request->session()->put('kintai',$search);






        // 勤怠情報を取得
        $query = Kintai::query();
        $query->select(
            'employees.code',
            'employees.order',
            DB::raw('SUM(kintais.work_hour) as work_hour_sum'),
        );
        $query->groupBy(
            'employees.code',
            'employees.order',
        );
        $query->where('kintais.client_id', Auth::id());

        // 社員の並び順にしたいので社員マスタをjoin
        $query->leftJoin('employees', 'kintais.employee_id', '=', 'employees.id');

        $query->whereDate('day', '>=', $start);
        $query->whereDate('day', '<=', $end);

        $kintais = $query->orderBy('order', 'ASC')->get();

        // カラムの作成
        $head = ['社員コード', '年度', '給与賞与区分', '支給月', '勤怠項目1'];

        $stream = fopen('php://temp', 'w');
        $data = [];
        mb_convert_variables('SJIS-win', 'UTF-8', $head);
        fputcsv($stream, $head);
        foreach ($kintais as $kintai) {
            $arr = [];
            $arr[] = $kintai->code;
            $arr[] = $year;
            $arr[] = '1';
            $arr[] = $month;
            $arr[] = $kintai->work_hour_sum;
            mb_convert_variables('SJIS-win', 'UTF-8', $arr);
            fputcsv($stream, $arr);
        }
        rewind($stream); //注意：fpassthru() する前にもファイルポインタは戻しておく

        // ダウンロード完了でリダイレクト用クッキー
        setcookie("downloaded", 1, time()+5);


        return response()->stream(function () use ($stream) { //修正 2. ストリームのままCSV出力できるようにする
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=smile_" . $year . sprintf('%02d', $month) . ".csv"
        ]);

        //return view('user.index', compact('users'));

    }



}
