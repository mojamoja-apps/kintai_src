@extends('adminlte::page')

@section('title', '勤怠打刻一覧')

@section('content_header')
    <h1>勤怠打刻一覧</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            {{Form::open(['method'=>'post', 'id'=>'search_form'])}}
                <div class="card card-default {{$collapse['CARD_CLASS']}}">
                    <div class="card-header">
                        <h3 class="card-title">検索</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool collapse_close" data-card-widget="collapse" data-animation-speed="300">
                                <i class="fas {{$collapse['BTN_CLASS']}}"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body" style="{{$collapse['BODY_STYLE']}}">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>日付</label>
                                    <div class="form-inline">
                                        <input type="text" class="form-control" name="day_st" id="day_st" placeholder="2022/01/01" value="{{ old('day_st', $search['day_st'] ) }}" style="width: 110px;">
                                        <span class="input-group-append">
                                            <button type="button" class="btn btn-info btn-flat day_today_btn" >今日</button>
                                        </span>
                                        <span class="m-1">～</span>
                                        <input type="text" class="form-control" name="day_ed" id="day_ed" placeholder="2022/12/31" value="{{ old('day_ed', $search['day_ed'] ) }}" style="width: 110px;">
                                        <span class="input-group-append">
                                            <button type="button" class="btn btn-info btn-flat day_today_btn" >今日</button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>社員</label>
                                    <select name="employee_id" id="employee_id" class="form-control select2" style="width: 100%;">
                                        <option value="" data-sub-search="">氏名・ふりがなで絞込</option>
@foreach($employees as $employee)
                                        <option value="{{$employee->id}}" data-sub-search="{{$employee->kana}}"
                                        @if ($search['employee_id'] == $employee->id) selected @endif
                                        >{{$employee->name}}</option>
@endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="form-inline">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="is_dakokumore" name="is_dakokumore" value="1"
                                            @if ($search['is_dakokumore'] == 1) checked @endif
                                            >
                                            <label for="is_dakokumore" class="custom-control-label">打刻漏れがある勤怠のみ</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-8">
                                <code>最新{{config('const.max_get')}}件が表示されています。それ以上表示したい場合は絞り込みを行ってください。</code>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-secondary float-right ml-1" onclick="clearSearchForm();">クリア</button>
                                <button type="submit" class="btn btn-primary float-right">検索</button>
                            </div>
                        </div>
                    </div>
                </div>
            {{ Form::close() }}
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table id="datatable1" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>日付</th>
                                <th>コード</th>
                                <th>氏名</th>
                                <th>出勤</th>
                                @if (Auth::user()->rest == 2)
                                <th>休憩開始</th>
                                <th>休憩終了</th>
                                @endif
                                @if (Auth::user()->rest == 3)
                                <th>休憩①開始</th>
                                <th>休憩①終了</th>
                                <th>休憩②開始</th>
                                <th>休憩②終了</th>
                                @endif
                                <th>退勤</th>
                                <th>勤務時間</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($kintais as $kintai)
                            <tr class="kintai_data_row">
                                <td>{{ $kintai->day !== null ?
                                        $kintai->day->format('m/d') . '(' . config('const.youbi.' . $kintai->day->format('w')) . ')'
                                        : '' }}</td>
                                <td>{{ $kintai->employee->code }}</td>
                                <td>{{ $kintai->employee->name }}</td>
                                <td>{{ $kintai->time_1 !== null ? $kintai->time_1->format('H:i') : '' }}</td>
                                @if (Auth::user()->rest == 2)
                                <td>{{ $kintai->time_2 !== null ? $kintai->time_2->format('H:i') : '' }}</td>
                                <td>{{ $kintai->time_3 !== null ? $kintai->time_3->format('H:i') : '' }}</td>
                                @endif
                                @if (Auth::user()->rest == 3)
                                <td>{{ $kintai->time_2 !== null ? $kintai->time_2->format('H:i') : '' }}</td>
                                <td>{{ $kintai->time_3 !== null ? $kintai->time_3->format('H:i') : '' }}</td>
                                <td>{{ $kintai->time_4 !== null ? $kintai->time_4->format('H:i') : '' }}</td>
                                <td>{{ $kintai->time_5 !== null ? $kintai->time_5->format('H:i') : '' }}</td>
                                @endif
                                <td>{{ $kintai->time_6 !== null ? $kintai->time_6->format('H:i') : '' }}</td>
                                <td class="text-right">99</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-primary" onclick="location.href='{{route('client.kintai.edit',['id' => $kintai->id])}}'">詳細</button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>


{{-- 検索クリアボタン用フォーム --}}
{{Form::open(['method'=>'post', 'id'=>'search_clear_form'])}}
{{ Form::close() }}

{{-- PDF出力用フォーム --}}
{{Form::open(['method'=>'post', 'id'=>'pdf_form'])}}
{{ Form::close() }}


@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/base/jquery-ui.min.css">
<link rel="stylesheet" href="{{ asset( cacheBusting('css/common.css') ) }}">
@stop

@section('js')
<script src="{{ asset( cacheBusting('js/common.js') ) }}"></script>
<script src="{{ asset( cacheBusting('js/client/kintai.js') ) }}"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jSignature/2.1.3/jSignature.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1/i18n/jquery.ui.datepicker-ja.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-input-spinner@1.9.7/src/bootstrap-input-spinner.js"></script>

<script>
$('#datatable1').DataTable({
    "language": {
        //"url": "//cdn.datatables.net/plug-ins/1.12.1/i18n/ja.json",
// JSON読み込みだとCSVボタンがでてこなくなってしまうので・・・
        "emptyTable": "テーブルにデータがありません",
        "info": " _TOTAL_ 件中 _START_ から _END_ まで表示",
        "infoEmpty": " 0 件中 0 から 0 まで表示",
        "infoFiltered": "（全 _MAX_ 件より抽出）",
        "infoThousands": ",",
        "lengthMenu": "_MENU_ 件表示",
        "loadingRecords": "読み込み中...",
        "processing": "処理中...",
        "search": "検索:",
        "zeroRecords": "一致するレコードがありません",
        "paginate": {
            "first": "先頭",
            "last": "最終",
            "next": "次",
            "previous": "前"
        },
        "aria": {
            "sortAscending": ": 列を昇順に並べ替えるにはアクティブにする",
            "sortDescending": ": 列を降順に並べ替えるにはアクティブにする"
        },
        "thousands": ",",
        "buttons": {
            "colvis": "項目の表示\/非表示",
            "csv": "CSV"
        },
        "searchBuilder": {
            "add": "条件を追加",
            "button": {
                "0": "カスタムサーチ",
                "_": "カスタムサーチ (%d)"
            },
            "clearAll": "すべての条件をクリア",
            "condition": "条件",
            "conditions": {
                "date": {
                    "after": "次の日付以降",
                    "before": "次の日付以前",
                    "between": "次の期間に含まれる",
                    "empty": "空白",
                    "equals": "次の日付と等しい",
                    "not": "次の日付と等しくない",
                    "notBetween": "次の期間に含まれない",
                    "notEmpty": "空白ではない"
                },
                "number": {
                    "between": "次の値の間に含まれる",
                    "empty": "空白",
                    "equals": "次の値と等しい",
                    "gt": "次の値よりも大きい",
                    "gte": "次の値以上",
                    "lt": "次の値未満",
                    "lte": "次の値以下",
                    "not": "次の値と等しくない",
                    "notBetween": "次の値の間に含まれない",
                    "notEmpty": "空白ではない"
                },
                "string": {
                    "contains": "次の文字を含む",
                    "empty": "空白",
                    "endsWith": "次の文字で終わる",
                    "equals": "次の文字と等しい",
                    "not": "次の文字と等しくない",
                    "notEmpty": "空白ではない",
                    "startsWith": "次の文字から始まる",
                    "notContains": "次の文字を含まない",
                    "notStarts": "次の文字で始まらない",
                    "notEnds": "次の文字で終わらない"
                }
            },
            "data": "項目",
            "title": {
                "0": "カスタムサーチ",
                "_": "カスタムサーチ (%d)"
            },
            "value": "値"
        }
// ここまで

    },
    "stateSave": true,
    "paging": true,
    "lengthChange": false,
    "searching": false,
    "ordering": true,
    "info": true,
    "autoWidth": false,
    "responsive": true,
    "columnDefs": [
        { responsivePriority: 1, targets: 0 },
        { responsivePriority: 2, targets: -1 },
        { responsivePriority: 3, targets: 1 },
        { targets: -1, width: "120px" },
    ],
    "buttons": ["csv"],
}).buttons().container().appendTo('#datatable1_wrapper .col-md-6:eq(0)');





$(function(){
    // 打刻が無いセルの色を塗る
    $.each($('.kintai_data_row td'), function (indexInArray, valueOfElement) {
         if ($(this).text().trim() === '') {
            $(this).addClass('bg-danger opacity-50');
         }
    });
})
</script>
@stop
