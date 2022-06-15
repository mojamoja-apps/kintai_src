@extends('adminlte::page')

@section('title', '出面集計表')

@section('content_header')
    <h1>出面集計表</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">

            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">条件指定</h3>
                </div>


                {{Form::open(['method'=>'post', 'files' => true, 'id'=>'edit_form', 'route' => 'admin.dedura.output'])}}
                    <div class="card-body">
                        <div class="form-group">
                            <label for="exampleInputEmail1">年月</label>
                            <div class="form-inline">
                                {{ Form::select('year', $years, old('year', $session['year'] ?? ''), ['class' => 'form-control']) }}
                                <span class="m-1">年</span>
                                {{ Form::select('month', $months, old('month', $session['month'] ?? ''), ['class' => 'form-control']) }}
                                <span class="m-1">月</span>
                            </div>
                            @if ($errors->has('year'))
                            <code>{{ $errors->first('year') }}</code>
                            @endif
                            @if ($errors->has('month'))
                            <code>{{ $errors->first('month') }}</code>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>元請け</label>
                            <select class="form-control" name="company_id" id="company_id">
                            </select>
                            @if ($errors->has('company_id'))
                            <code>{{ $errors->first('company_id') }}</code>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>作業所</label>
                            <select class="form-control" name="site_id" id="site_id">
                            </select>
                            @if ($errors->has('site_id'))
                            <code>{{ $errors->first('site_id') }}</code>
                            @endif
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" id="commit_btn" class="btn btn-primary">Excel出力</button>
                        <code>ダウンロードまでに時間がかかりますが一度だけクリックしてお待ちください。</code>
                    </div>

                {{ Form::close() }}


                <div class="overlay dark" id="overlay_spin" style="display:none;">
                    <i class="fas fa-2x fa-sync-alt fa-spin"></i>
                </div>
            </div>
        </div>
    </div>
</div>

@stop




@section('css')
<link rel="stylesheet" href="{{ asset( cacheBusting('css/common.css') ) }}">
@stop

@section('js')
<script src="{{ asset( cacheBusting('js/common.js') ) }}"></script>

<script>
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
    $("#company_id").val({{ old('company_id', $session['company_id'] ?? '') }});
    $("#company_id").change();
    $("#site_id").val({{ old('site_id', $session['site_id'] ?? '') }});




    $("#edit_form").submit(function (e) {
        setInterval(function () {
            if ($.cookie("downloaded")) {
                $.removeCookie("downloaded", { path: "/" });
                location.href = "{{ route('admin.dedura.index') }}";
            }
        }, 1000);

        $("#overlay_spin").show();
        $("#commit_btn").prop("disabled", true);
    });
})
</script>
@stop
