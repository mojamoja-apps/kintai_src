<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Cookie;

use App\Models\Company;
use App\Models\Site;
use App\Models\Worker;
use App\Services\DeduraService;
use App\Services\DatetimeUtility;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\File;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

use Symfony\Component\HttpFoundation\BinaryFileResponse;


class DeduraController extends Controller
{
    public $companies;
    public $sites;
    public $workers;


    function __construct() {
        // 元請け一覧
        $companies = Company::all()->sortBy('id');
        // key,value ペアに直す
        $this->companies = $companies->pluck('name','id')->prepend( "選択してください", "");

        // 作業所一覧
        $sites = Site::all()->sortBy('id');
        // key,value ペアに直す
        $this->sites = $sites->pluck(null,'id')->toArray();

        // 作業員一覧
        $workers = Worker::all()->sortBy('id');
        // key,value ペアに直す
        $this->workers = $workers->pluck('name','id')->prepend( "", "");
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
        $session = $session['dedura'] ?? [];

        $companies = $this->companies;
        $sites = $this->sites;

        return view('admin/dedura/index', compact('years', 'months', 'companies', 'sites', 'session'));
    }

    // Excel出力
    public function output(Request $request) {
        $request->validate([
            'year' => 'required',
            'month' => 'required',
            'company_id' => 'required',
            'site_id' => 'required',
        ]
        ,[
            'year.required' => '必須項目です。',
            'month.required' => '必須項目です。',
            'company_id.required' => '必須項目です。',
            'site_id.required' => '必須項目です。',
        ]);

        $year = $request->input('year');
        $month = $request->input('month');
        $company_id = $request->input('company_id');
        $company = Company::find($company_id);
        $site_id = $request->input('site_id');
        $site = Site::find($site_id);


        // セッションを一旦消して検索値を保存
        $session = $request->session()->get('dedura');
        $search = [];
        $search['year'] = $request->input('year');
        $search['month'] = $request->input('month');
        $search['company_id'] = $request->input('company_id');
        $search['site_id'] = $request->input('site_id');
        $request->session()->forget('dedura');
        $request->session()->put('dedura',$search);



        //対象年月の月初、月末を求める
        $start = new Carbon("{$year}/{$month}/01");
        $end = new Carbon("{$year}/{$month}/01");
        $end->lastOfMonth();    // endOfMonthでは23時59分



        // Excel用意
        $spreadsheet = IOFactory::load(resource_path() . '/excel/dedura_template.xlsx');
        $sheet = $spreadsheet->getActiveSheet();
        // データ入力 6行目から開始
        $rowNo = 6;
        // 自社+外注2社分は1、以降は2を使う
        $syahoRowNo1 = 46;
        $syahoRowNo2 = 87;

        $st = Carbon::parse($site->period_st);
        $ed = Carbon::parse($site->period_ed);
        $kouki = DatetimeUtility::date('bk n/j', $st->timestamp) . '～' . DatetimeUtility::date('n/j', $ed->timestamp);
        // 工期 R4 2/10～3/31
        $sheet->setCellValue("AJ3", $kouki);

        // 元請け名
        $sheet->setCellValue("E2", $site->company->name);
        // 現場名
        $sheet->setCellValue("E3", $site->name);

        $deduraService = New DeduraService();
        $bufday = clone $start;
        while(true) {

            $ret = $deduraService->getDeduraByDay($company_id, $site_id, $bufday);

            $sheet->setCellValue("A{$rowNo}", $bufday->format('m/d'));
            if ($ret !== null) {

                $sheet->setCellValue("C{$rowNo}", $ret->koji_1_memo);
                $sheet->setCellValue("L{$rowNo}", $ret->koji_2_memo);
                $sheet->setCellValue("W{$rowNo}", $ret->koji_3_memo);

                $name = config('const.KOJI.KOJI_KBN_LIST_NAME.' . $ret->koji_4_kbn) ?? '';
                if ($name != '') $name .= PHP_EOL;
                $sheet->setCellValue("AH{$rowNo}", $name . $ret->koji_4_memo);

                $name = config('const.KOJI.KOJI_KBN_LIST_NAME.' . $ret->koji_5_kbn) ?? '';
                if ($name != '') $name .= PHP_EOL;
                $sheet->setCellValue("AO{$rowNo}", $name . $ret->koji_5_memo);

                // 工事5種繰り返し
                // 鳶、土工 員数、早残合計5行分合計
                $num_cells = ['H', 'J', 'O', 'Q', 'Z', 'AB', 'AK', 'AM', 'AR', 'AT'];
                $sozan_cells = ['I', 'K', 'P', 'R', 'AA', 'AC', 'AL', 'AN', 'AS', 'AU'];
                $cell_key = 0;
                foreach(config('const.KOJINAMES') as $kojikey => $kojiname) {
                    foreach(config('const.TOBI_DOKO') as $tobidokokey => $tobidokoname) {

                        $sum =
                            intval($ret->{"koji_{$kojikey}_{$tobidokokey}_1_num"})
                            + intval($ret->{"koji_{$kojikey}_{$tobidokokey}_2_num"})
                            + intval($ret->{"koji_{$kojikey}_{$tobidokokey}_3_num"})
                            + intval($ret->{"koji_{$kojikey}_{$tobidokokey}_4_num"})
                            + intval($ret->{"koji_{$kojikey}_{$tobidokokey}_5_num"})
                        ;
                        $output = $sum == 0 ? '' : $sum;
                        $sheet->setCellValue("{$num_cells[$cell_key]}{$rowNo}", $output);

                        $sum =
                            floatval($ret->{"koji_{$kojikey}_{$tobidokokey}_1_sozan"})
                            + floatval($ret->{"koji_{$kojikey}_{$tobidokokey}_2_sozan"})
                            + floatval($ret->{"koji_{$kojikey}_{$tobidokokey}_3_sozan"})
                            + floatval($ret->{"koji_{$kojikey}_{$tobidokokey}_4_sozan"})
                            + floatval($ret->{"koji_{$kojikey}_{$tobidokokey}_5_sozan"})
                        ;
                        $output = $sum == 0 ? '' : $sum;
                        $sheet->setCellValue("{$sozan_cells[$cell_key]}{$rowNo}", $output);

                        $cell_key++;
                    }
                }

                // ポンプ
                $sum =
                    intval($ret->koji_2_car_1_num)
                    + intval($ret->koji_2_car_2_num)
                ;
                $output = $sum == 0 ? '' : $sum;
                $sheet->setCellValue("S{$rowNo}", $output);

                // 打設量
                $output = $ret->koji_2_dasetu === null ? '' : $ret->koji_2_dasetu;
                $sheet->setCellValue("U{$rowNo}", $output);

                // 重機
                $sum =
                    intval($ret->koji_3_car_1_num)
                    + intval($ret->koji_3_car_2_num)
                    + intval($ret->koji_3_car_3_num)
                ;
                $output = $sum == 0 ? '' : $sum;
                $sheet->setCellValue("AD{$rowNo}", $output);

                // ダンプ
                $sum =
                    intval($ret->koji_3_car_4_num)
                    + intval($ret->koji_3_car_5_num)
                ;
                $output = $sum == 0 ? '' : $sum;
                $sheet->setCellValue("AF{$rowNo}", $output);





                // 出面集計表追加分■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□
                // 所属会社名をセット
                $targets = [
                    2 => 'AH43',
                    3 => 'AP43',
                    4 => 'B84',
                    5 => 'J84',
                    6 => 'R84',
                    7 => 'Z84',
                    8 => 'AH84',
                    9 => 'AP84',
                ];
                foreach (config('const.belongs') as $belongkey => $belongname) {
                    // 自社はスルー
                    if ($belongkey == 1) continue;
                    $sheet->setCellValue($targets[$belongkey], $belongname);
                }



                $syaho = [];
                if ($ret->reportworkings != null) {
                    foreach ($ret->reportworkings as $workerkey => $reportworking) {
                        if ($reportworking->worker != null) {
                            // 正社員のみ
                            if ($reportworking->worker->style == 1) {
                                // 所属・社保ありなし・鳶土工 で振り分ける
                                if (!isset($syaho[$reportworking->worker->belongs][$reportworking->worker->insurance][$reportworking->tobidoko])) {
                                    $syaho[$reportworking->worker->belongs][$reportworking->worker->insurance][$reportworking->tobidoko]['num'] = 0;
                                    $syaho[$reportworking->worker->belongs][$reportworking->worker->insurance][$reportworking->tobidoko]['sozan'] = 0;
                                }
                                $syaho[$reportworking->worker->belongs][$reportworking->worker->insurance][$reportworking->tobidoko]['num']++;
                                $syaho[$reportworking->worker->belongs][$reportworking->worker->insurance][$reportworking->tobidoko]['sozan'] += $reportworking->sozan;
                            }
                        }
                    }
                }

                // 自社・協力会社のセル設定
                // 所属・社保・鳶土工
                $belongs_column_list = [];
                for ($ix = 1; $ix <= count(config('const.belongs')); $ix++) {
                    if ($ix == 1) {
                        // 3社めまでは1ページ目、Z=26からスタート インクリメントするのでマイナス1して25
                        $colcnt = 25;
                        $page = 1;
                    } else if ($ix == 4) {
                        // 3社以降2ページ目、 B=2からスタート インクリメントするのでマイナス1して1
                        $colcnt = 1;
                        $page = 2;
                    }
                    for ($iy = 1; $iy <= 2; $iy++) {
                        for ($iz = 1; $iz <= 2; $iz++) {
                            $colcnt++;
                            $col1 = Coordinate::stringFromColumnIndex($colcnt);
                            $colcnt++;
                            $col2 = Coordinate::stringFromColumnIndex($colcnt);
                            $belongs_column_list[$ix][$iy][$iz] = ['col1' => $col1, 'col2' => $col2, 'page' => $page];
                        }
                    }
                }

                foreach ($syaho as $belongs => $arr1) {
                    foreach ($arr1 as $insurance => $arr2) {
                        foreach ($arr2 as $tobidoko => $data) {

                            $col1 = $belongs_column_list[$belongs][$insurance][$tobidoko]['col1'];
                            $col2 = $belongs_column_list[$belongs][$insurance][$tobidoko]['col2'];
                            $page = $belongs_column_list[$belongs][$insurance][$tobidoko]['page'];
                            if ($page == 1) {
                                $row = $syahoRowNo1;
                            } else if ($page == 2) {
                                $row = $syahoRowNo2;
                            }
                            $sheet->setCellValue("{$col1}{$row}", $data['num']);

                            $sheet->setCellValue("{$col2}{$row}", $data['sozan']);

                        }
                    }
                }

            }



            if ($bufday >= $end) break;
            $bufday->addDay();
            $rowNo++;
            $syahoRowNo1++;
            $syahoRowNo2++;
        }










        File::setUseUploadTempDirectory(resource_path());

        $writer = new Xlsx($spreadsheet);
        $writer->save(resource_path() . '/excel/output.xlsx');                     // (d)

        // ダウンロード完了でリダイレクト用クッキー
        setcookie("downloaded", 1, time()+5);

        return response()->download(resource_path() . '/excel/output.xlsx', '出面集計表_' . $company->name . '_' . $site->name . '_' . $year . $month . '.xlsx',
                               ['content-type' => 'application/vnd.ms-excel',])  // (e)
                         ->deleteFileAfterSend(true);                            // (f)

    }
}
