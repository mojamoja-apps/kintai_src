@extends('adminlte::page')

@section('title', '勤怠打刻')

@section('content_header')

@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default mt-1">
                <div class="card-header">
                    <h3 class="card-title">{{ $client->name }} 勤怠入力</h3>
                </div>
                <div class="card-body">
                    <p class="text-center display-1">
                        <strong id="time"></strong>
                    </p>
                    <div class="form-group">
                        <select name="employee" id="employee" class="form-control select2" style="width: 100%;">
                            <option value="" data-sub-search="">氏名・ふりがなで絞込</option>
@foreach($employees as $employee)
                            <option value="{{$employee->id}}" data-sub-search="{{$employee->kana}}">{{$employee->name}}</option>
@endforeach
                        </select>
                    </div>
                    <div class="form-group col-lg-12">
                        <button type="button" id="kintai_btn_1" data-dakokumode="1" class="btn btn-block btn-lg btn-primary">出勤</button>
                        <button type="button" id="kintai_btn_2" data-dakokumode="2" class="btn btn-block btn-lg btn-success">休憩①入</button>
                        <button type="button" id="kintai_btn_3" data-dakokumode="3" class="btn btn-block btn-lg btn-info">休憩①出</button>
                        <button type="button" id="kintai_btn_4" data-dakokumode="4" class="btn btn-block btn-lg btn-secondary">休憩②入</button>
                        <button type="button" id="kintai_btn_5" data-dakokumode="5" class="btn btn-block btn-lg btn-warning">休憩②出</button>
                        <button type="button" id="kintai_btn_6" data-dakokumode="6" class="btn btn-block btn-lg btn-danger">退勤</button>
                    </div>
                </div>
                <div class="overlay dark" id="overlay_spin" style="display: none;">
                    <i class="fas fa-2x fa-sync-alt fa-spin"></i>
                </div>
            </div>
        </div>

    </div>
</div>





@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/base/jquery-ui.min.css">
<link rel="stylesheet" href="{{ asset( cacheBusting('css/common.css') ) }}">
@stop

@section('js')
<script src="{{ asset( cacheBusting('js/common.js') ) }}"></script>
<script src="{{ asset( cacheBusting('js/kintai/kintai.js') ) }}"></script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1/i18n/jquery.ui.datepicker-ja.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-input-spinner@1.9.7/src/bootstrap-input-spinner.js"></script>


<script>



</script>

@stop
