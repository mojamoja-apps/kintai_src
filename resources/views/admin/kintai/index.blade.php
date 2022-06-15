@extends('adminlte::page')

@section('title', '勤怠管理表')

@section('content_header')
    <h1>勤怠管理表</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">

            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">条件指定</h3>
                </div>


                {{Form::open(['method'=>'post', 'files' => true, 'id'=>'edit_form', 'route' => 'admin.kintai.output'])}}
                    <div class="card-body">
                        <div class="form-group">
                            <label for="exampleInputEmail1">年月</label>
                            <div class="form-inline">
                                {{ Form::select('year', $years, old('year', $session['year'] ?? ''), ['class' => 'form-control']) }}
                                <span class="m-1">年</span>
                                {{ Form::select('month', $months, old('month', $session['month'] ?? ''), ['class' => 'form-control']) }}
                                <span class="m-1">月</span>

                                <span class="m-2">26日～翌月25日</span>
                            </div>
                            @if ($errors->has('year'))
                            <code>{{ $errors->first('year') }}</code>
                            @endif
                            @if ($errors->has('month'))
                            <code>{{ $errors->first('month') }}</code>
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

    $("#edit_form").submit(function (e) {
        setInterval(function () {
            if ($.cookie("downloaded")) {
                $.removeCookie("downloaded", { path: "/" });
                location.href = "{{ route('admin.kintai.index') }}";
            }
        }, 1000);

        $("#overlay_spin").show();
        $("#commit_btn").prop("disabled", true);
    });
})
</script>
@stop
