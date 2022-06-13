@extends('adminlte::page')

@section('title', '作業証明書一覧')

@section('content_header')
    <h1>作業証明書一覧</h1>
@stop

@section('content')
<div class="form-group mt-15">
    <button type="button" class="btn btn-primary" onclick="location.href='{{ route('report.edit') }}'">新規登録</button>
</div>

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
                                    <label>元請け</label>
                                    <select class="form-control" name="company_id" id="company_id">
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>作業所</label>
                                    <select class="form-control" name="site_id" id="site_id">
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>フリーワード検索</label>
                                    <input type="search" class="form-control" placeholder="スペース区切りで複数キーワード" name="keyword" value="{{$search['keyword']}}">
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
                                <th>ID</th>
                                <th>日付</th>
                                <th>元請け</th>
                                <th>作業所</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($reports as $report)
                            <tr>
                                <td>{{ $report->id }}</td>
                                <td>{{ $report->day !== null ? $report->day->format('Y/m/d') : '' }}</td>
                                <td>{{ $report->company->name ?? '' }}</td>
                                <td>{{ $report->site->name ?? '' }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-primary" onclick="location.href='{{route('report.edit',['id' => $report->id])}}'">編集</button>
                                    <button type="button" class="btn btn-danger delete_btn" onclick="deleteData('{{ route('report.destroy',['id' => $report->id]) }}');">削除</button>
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


{{-- 削除ボタン用フォーム --}}
{{Form::open(['method'=>'post', 'id'=>'delete_form'])}}
{{ Form::close() }}


@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/base/jquery-ui.min.css">
<link rel="stylesheet" href="{{ asset( cacheBusting('css/common.css') ) }}">
@stop

@section('js')
<script src="{{ asset( cacheBusting('js/common.js') ) }}"></script>
<script src="{{ asset( cacheBusting('js/report/report.js') ) }}"></script>

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


    // 元請けの変更で作業所一覧を再生成
    $("#company_id").change(function() {
        selChange(this);
    });
    //元請けの選択肢
    var parents = [
@foreach ($companies as $key => $company)
        {cd:"{{$key}}", label:"{{$company}}"},
@endforeach
    ];

    //作業所の選択肢
    var children = [];
@foreach ($companies as $comkey => $company)
    @if ($comkey == '') @continue;
    @endif
    children[{{$comkey}}] = [
        {cd:"", label:"選択してください"},
    @foreach ($sites as $sitekey => $site)
        @if ($comkey == $site['company_id'])
        {cd:"{{$sitekey}}", label:"{{$site['name']}}"},
        @endif
    @endforeach
//1行開けないと反映されない？
    ];
@endforeach

    //元請けコンボの生成
    for(var i=0;i<parents.length;i++){
        let op = document.createElement("option");
        op.value = parents[i].cd;
        op.text = parents[i].label;
        document.getElementById("company_id").appendChild(op);
    }

    //元請けが選択された時に呼び出される処理
    function selChange(obj){
        var targetArr = children[obj.value];
        var selObj = document.getElementById('site_id');
        while(selObj.lastChild){
            selObj.removeChild(selObj.lastChild);
        }
        if (targetArr !== undefined) {
            for(var i=0;i<targetArr.length;i++){
                let op = document.createElement("option");
                op.value = targetArr[i].cd;
                op.text = targetArr[i].label;
                selObj.appendChild(op);
            }
        }
    }

    // 元請け、作業所を初期選択
    $("#company_id").val({{ old('company_id', $search['company_id']) }});
    $("#company_id").change();
    $("#site_id").val({{ old('site_id', $search['site_id']) }});
})
</script>
@stop
