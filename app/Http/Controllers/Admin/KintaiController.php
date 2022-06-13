<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Cookie;

use App\Models\Company;
use App\Models\Site;
use App\Models\Worker;
use App\Services\KintaiService;
use App\Services\DatetimeUtility;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\File;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style;
use PhpOffice\PhpSpreadsheet\Style\Fill;

use Symfony\Component\HttpFoundation\BinaryFileResponse;


class KintaiController extends Controller
{
    public $sheet;

    function __construct() {

    }

    // 一覧
    public function index() {
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

        return view('admin/kintai/index', compact('years', 'months', 'session'));
    }

    // Excel出力
    public function output(Request $request) {
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

        $workers = Worker::where('style', '=', '1')->orderBy('id', 'asc')->get();  // 1:正社員
        $parts = Worker::where('style', '=', '2')->orderBy('id', 'asc')->get();  // 2:アルバイト

        // セッションを一旦消して検索値を保存
        $session = $request->session()->get('kintai');
        $search = [];
        $search['year'] = $request->input('year');
        $search['month'] = $request->input('month');
        $request->session()->forget('kintai');
        $request->session()->put('kintai',$search);



        //対象年月の26日～翌月25日を求める
        $start = new Carbon("{$year}/{$month}/26");
        $end = new Carbon("{$year}/{$month}/01");
        $end->addMonthsNoOverflow(1);
        $nextmonth = $end->month;
        $end = new Carbon("{$year}/{$nextmonth}/25");


        // Excel用意
        $spreadsheet = IOFactory::load(resource_path() . '/excel/kintai_template.xlsx');
        // 作業シート
        $this->sheet = $spreadsheet->getActiveSheet();

        // データ入力 4行目から開始
        $start_rowNo = 4;

        // Ⅰ人あたりのデータの行数
        $offset = 5;
        $offset_part = 2;

        // 作業中行番号
        $current_rowno = 0;

        $st = Carbon::parse($start);
        $dt = DatetimeUtility::date('bk年 n月', $st->timestamp);
        // R4 2/10～3/31
        $this->sheet->setCellValue("B2", $dt);


        //$kintaiService = New KintaiService();

        // Ⅰヶ月分日付をセット
        $bufday = clone $start;
        $this->dayRowOutput(3, $bufday, $end);

        // 正社員繰り返し
        $current_rowno = $start_rowNo;
        foreach ($workers as $workerkey => $worker) {

            // 最初以外 行番号設定
            if ($workerkey != 0) {
                $current_rowno = $current_rowno + $offset;
            }
            $kbn = $this->sheet->getCell("A{$current_rowno}")->getValue();
            if ($kbn == 9) {
                // 見出し行は日付をセット
                $bufday = clone $start;
                $this->dayRowOutput($current_rowno, $bufday, $end);
                $current_rowno++;
            }

            $rowNo = [];
            $rowNo[1] = $current_rowno;
            $rowNo[2] = $current_rowno + 1;
            $rowNo[3] = $current_rowno + 2;
            $rowNo[4] = $current_rowno + 3;
            $rowNo[5] = $current_rowno + 4;

            $this->sheet->setCellValue("B{$rowNo[1]}", $worker->name);
            $this->sheet->setCellValue("AK{$rowNo[1]}", $worker->name);

            // 鳶 サブクエリ
            $sub_query1 = DB::table("report_workings")
                ->select(DB::raw('
                    report_id
                    , COUNT(*) AS tobi_cnt
                    , SUM(COALESCE(sozan,0)) AS tobi_sozan
                '))
                ->where('tobidoko', 1)
                ->where('worker_id', $worker->id)
                ->groupBy('report_id');
            // 土工 サブクエリ
            $sub_query2 = DB::table("report_workings")
                ->select(DB::raw('
                    report_id
                    , COUNT(*) AS doko_cnt
                    , SUM(COALESCE(sozan,0)) AS doko_sozan
                '))
                ->where('tobidoko', 2)
                ->where('worker_id', $worker->id)
                ->groupBy('report_id');
            // 運転手 サブクエリ
            $sub_query3 = DB::table("report_drivers")
                ->select(DB::raw('
                    report_id
                    , COUNT(*) AS driver_cnt
                '))
                ->where('worker_id', $worker->id)
                ->groupBy('report_id');


            $dbdatas = DB::table('reports')
                ->leftJoinSub($sub_query1, 'tobiwork', 'reports.id', '=', 'tobiwork.report_id')
                ->leftJoinSub($sub_query2, 'dokowork', 'reports.id', '=', 'dokowork.report_id')
                ->leftJoinSub($sub_query3, 'driver', 'reports.id', '=', 'driver.report_id')
                ->select(DB::raw('
                    day
                    ,tobi_cnt
                    ,tobi_sozan
                    ,doko_cnt
                    ,doko_sozan
                    ,driver_cnt
                '))

                ->whereBetween('day', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                ->orderBy('day', 'asc')
                ->get();

            // 日付毎に繰り返し
            $col_cnt = 0;
            $bufday = clone $start;
            while(true) {
                // Dからスタート +4
                $ColumnName = Coordinate::stringFromColumnIndex($col_cnt+4);

                // 日曜だったら色を赤く
                if ($bufday->isSunday()) {
                    $objStyle = $this->sheet->getStyle("{$ColumnName}{$rowNo[1]}:{$ColumnName}{$rowNo[5]}");
                    // フィルオブジェクト取得
                    $objFill = $objStyle->getFill();
                    // 背景のタイプを「塗つぶし」に設定
                    $objFill->setFillType(Fill::FILL_SOLID);
                    // 背景色を「赤」に設定
                    $objFill->getStartColor()->setARGB('FFCC0000');
                }

                $done = false;
                $sozan = 0;
                $driver = 0;
                foreach ($dbdatas as $dbdata) {
                    // 作業証明書あり・出勤ありなら
                    if (
                        $bufday->format('Y-m-d') == $dbdata->day
                        && (!empty($dbdata->tobi_cnt) || !empty($dbdata->doko_cnt) || !empty($dbdata->driver_cnt))
                    ) {
                        $this->sheet->setCellValue("{$ColumnName}{$rowNo[1]}", ($dbdata->tobi_cnt + $dbdata->doko_cnt));
                        $done = true;

                        if ($dbdata->tobi_cnt > 0) {
                            $sozan += $dbdata->tobi_sozan;
                        }
                        if ($dbdata->doko_cnt > 0) {
                            $sozan += $dbdata->doko_sozan;
                        }
                        $driver += $dbdata->driver_cnt;

                        break;
                    }
                }


                if ($done == false) {
                    //出勤が出力されていない、日曜以外なら休と出力
                    if ($bufday->isSunday() == false) {
                        $this->sheet->setCellValue("{$ColumnName}{$rowNo[1]}", '休');
                    }
                }
                if ($sozan > 0) {
                    $this->sheet->setCellValue("{$ColumnName}{$rowNo[2]}", $sozan);
                }
                if ($driver > 0) {
                    $this->sheet->setCellValue("{$ColumnName}{$rowNo[3]}", $driver);
                }

                // 宿 常に1
                $this->sheet->setCellValue("{$ColumnName}{$rowNo[4]}", 1);
                // 食 日曜はなし
                if ($bufday->isSunday() == false) {
                    $this->sheet->setCellValue("{$ColumnName}{$rowNo[5]}", 1);
                }

                if ($bufday >= $end) break;
                $bufday->addDay();
                $col_cnt++;
            }

            continue;
        }

        // 次の人の行へ移動してから
        $current_rowno += $offset;
        $dayrow_done = false;
        while(true) {
            $kbn = $this->sheet->getCell("A{$current_rowno}")->getValue();
            // Aセルの値が1の間は削除を繰り返す
            if ($kbn == 1) {
                //削除だと結合が壊れるので $this->sheet->removeRow($current_rowno);
                $this->sheet->getRowDimension($current_rowno)->setVisible(false);
                $current_rowno++;
            } else if ($kbn == 9 && $dayrow_done == false) {
                // 見出し行は日付をセット
                $bufday = clone $start;
                $this->dayRowOutput($current_rowno, $bufday, $end);
                $current_rowno++;
                // 見出し行 一回やったらもうやらない
                $dayrow_done = true;
            } else if ($kbn == 9 && $dayrow_done == true) {
                //削除だと結合が壊れるので $this->sheet->removeRow($current_rowno);
                $this->sheet->getRowDimension($current_rowno)->setVisible(false);
                $current_rowno++;
            } else {
                // 2 アルバイト行が来たら終了
                break;
            }
        }



















        // アルバイト
        foreach ($parts as $workerkey => $worker) {

            // 最初以外 行番号設定
            if ($workerkey != 0) {
                $current_rowno = $current_rowno + $offset_part;
            }

            $kbn = $this->sheet->getCell("A{$current_rowno}")->getValue();
            if ($kbn == 9) {
                // 見出し行は日付をセット
                $bufday = clone $start;
                $this->dayRowOutput($current_rowno, $bufday, $end);
                $current_rowno++;
            }

            $rowNo = [];
            $rowNo[1] = $current_rowno;
            $rowNo[2] = $current_rowno + 1;

            $this->sheet->setCellValue("B{$rowNo[1]}", $worker->name);
            $this->sheet->setCellValue("AK{$rowNo[1]}", $worker->name);

            // 鳶 サブクエリ
            $sub_query1 = DB::table("report_workings")
                ->select(DB::raw('
                    report_id
                    , COUNT(*) AS tobi_cnt
                    , SUM(COALESCE(sozan,0)) AS tobi_sozan
                '))
                ->where('tobidoko', 1)
                ->where('worker_id', $worker->id)
                ->groupBy('report_id');
            // 土工 サブクエリ
            $sub_query2 = DB::table("report_workings")
                ->select(DB::raw('
                    report_id
                    , COUNT(*) AS doko_cnt
                    , SUM(COALESCE(sozan,0)) AS doko_sozan
                '))
                ->where('tobidoko', 2)
                ->where('worker_id', $worker->id)
                ->groupBy('report_id');

            $dbdatas = DB::table('reports')
                ->leftJoinSub($sub_query1, 'tobiwork', 'reports.id', '=', 'tobiwork.report_id')
                ->leftJoinSub($sub_query2, 'dokowork', 'reports.id', '=', 'dokowork.report_id')
                ->select(DB::raw('
                    day
                    ,tobi_cnt
                    ,tobi_sozan
                    ,doko_cnt
                    ,doko_sozan
                '))

                ->whereBetween('day', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                ->orderBy('day', 'asc')
                ->get();

            // 日付毎に繰り返し
            $col_cnt = 0;
            $bufday = clone $start;
            while(true) {
                // Dからスタート +4
                $ColumnName = Coordinate::stringFromColumnIndex($col_cnt+4);

                // 日曜だったら色を赤く
                if ($bufday->isSunday()) {
                    $objStyle = $this->sheet->getStyle("{$ColumnName}{$rowNo[1]}:{$ColumnName}{$rowNo[2]}");
                    // フィルオブジェクト取得
                    $objFill = $objStyle->getFill();
                    // 背景のタイプを「塗つぶし」に設定
                    $objFill->setFillType(Fill::FILL_SOLID);
                    // 背景色を「赤」に設定
                    $objFill->getStartColor()->setARGB('FFCC0000');
                }

                $done = false;
                $sozan = 0;
                foreach ($dbdatas as $dbdata) {
                    // 作業証明書あり・出勤ありなら
                    if (
                        $bufday->format('Y-m-d') == $dbdata->day
                        && (!empty($dbdata->tobi_cnt) || !empty($dbdata->doko_cnt))
                    ) {
                        // 出勤数×8時間 + 早残を時間として表示
                        $num = $dbdata->tobi_cnt + $dbdata->doko_cnt;
                        if ($dbdata->tobi_cnt > 0) {
                            $sozan += $dbdata->tobi_sozan;
                        }
                        if ($dbdata->doko_cnt > 0) {
                            $sozan += $dbdata->doko_sozan;
                        }

                        $this->sheet->setCellValue("{$ColumnName}{$rowNo[1]}", ($num*8)+$sozan);

                        // 出勤数を表示
                        $this->sheet->setCellValue("{$ColumnName}{$rowNo[2]}", $num);
                        $done = true;


                        break;
                    }
                }

                if ($done == false) {
                    //出勤が出力されていない、日曜以外なら自己都合と出力
                    if ($bufday->isSunday() == false) {
                        $this->sheet->setCellValue("{$ColumnName}{$rowNo[1]}", '自都');
                    }
                }

                if ($bufday >= $end) break;
                $bufday->addDay();
                $col_cnt++;
            }

            continue;
        }

        // 次の人の行へ移動してから
        $current_rowno += $offset_part;
        $dayrow_done = false;
        while(true) {
            $kbn = $this->sheet->getCell("A{$current_rowno}")->getValue();
            // Aセルの値が2の間は削除を繰り返す
            if ($kbn == 2) {
                //削除だと結合が壊れるので $this->sheet->removeRow($current_rowno);
                $this->sheet->getRowDimension($current_rowno)->setVisible(false);
                $current_rowno++;
            } else if ($kbn == 9 && $dayrow_done == false) {
                // 見出し行は日付をセット
                $bufday = clone $start;
                $this->dayRowOutput($current_rowno, $bufday, $end);
                $current_rowno++;
                // 見出し行 一回やったらもうやらない
                $dayrow_done = true;
            } else if ($kbn == 9 && $dayrow_done == true) {
                //削除だと結合が壊れるので $this->sheet->removeRow($current_rowno);
                $this->sheet->getRowDimension($current_rowno)->setVisible(false);
                $current_rowno++;
            } else {
                // 2 アルバイト行が来たら終了
                break;
            }
        }

        File::setUseUploadTempDirectory(resource_path());

        $writer = new Xlsx($spreadsheet);
        $writer->save(resource_path() . '/excel/kintai.xlsx');

        // ダウンロード完了でリダイレクト用クッキー
        setcookie("downloaded", 1, time()+5);

        return response()->download(resource_path() . '/excel/kintai.xlsx', '勤怠管理表_' . $year . $month . '.xlsx',
                               ['content-type' => 'application/vnd.ms-excel',])
                         ->deleteFileAfterSend(true);

    }





    private function dayRowOutput($targetrow, $bufday, $end) {
        // Ⅰヶ月分日付をセット
        $col_cnt = 0;
        while(true) {
            // Dからスタート +4
            $ColumnName = Coordinate::stringFromColumnIndex($col_cnt+4);

            // 日曜だったら色を赤く
            if ($bufday->isSunday()) {
                $objStyle = $this->sheet->getStyle("{$ColumnName}{$targetrow}");
                // フィルオブジェクト取得
                $objFill = $objStyle->getFill();
                // 背景のタイプを「塗つぶし」に設定
                $objFill->setFillType(Fill::FILL_SOLID);
                // 背景色を「赤」に設定
                $objFill->getStartColor()->setARGB('FFCC0000');
            }

            $this->sheet->setCellValue("{$ColumnName}{$targetrow}", $bufday->day);
            if ($bufday >= $end) break;
            $bufday->addDay();
            $col_cnt++;
        }
    }
}
