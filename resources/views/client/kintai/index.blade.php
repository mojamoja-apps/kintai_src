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
                                    <select name="employee" id="employee" class="form-control select2" style="width: 100%;">
                                        <option value="" data-sub-search="">氏名・ふりがなで絞込</option>
@foreach($employees as $employee)
                                        <option value="{{$employee->id}}" data-sub-search="{{$employee->kana}}">{{$employee->name}}</option>
@endforeach
                                    </select>
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
                                <th>氏名</th>
                                <th>出勤</th>
                                <th>休憩①開始</th>
                                <th>休憩①終了</th>
                                <th>休憩②開始</th>
                                <th>休憩②終了</th>
                                <th>退勤</th>
                                <th>勤務時間</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($kintais as $kintai)
                            <tr class="kintai_data_row">
                                <td>{{ $kintai->day !== null ? $kintai->day->format('Y/m/d') : '' }}</td>
                                <td>{{ $kintai->employee->name }}</td>
                                <td>{{ $kintai->time_1 !== null ? $kintai->time_1->format('h:i') : '' }}</td>
                                <td>{{ $kintai->time_2 !== null ? $kintai->time_2->format('h:i') : '' }}</td>
                                <td>{{ $kintai->time_3 !== null ? $kintai->time_3->format('h:i') : '' }}</td>
                                <td>{{ $kintai->time_4 !== null ? $kintai->time_4->format('h:i') : '' }}</td>
                                <td>{{ $kintai->time_5 !== null ? $kintai->time_5->format('h:i') : '' }}</td>
                                <td>{{ $kintai->time_6 !== null ? $kintai->time_6->format('h:i') : '' }}</td>
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
        "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Japanese.json",
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
});






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
