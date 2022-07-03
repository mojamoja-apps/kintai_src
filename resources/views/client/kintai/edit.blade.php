@extends('adminlte::page')

@section('title', '勤怠打刻修正')

@section('content_header')
    <h1>勤怠打刻修正</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-6">
            <div class="card card-primary">

                @if ($mode == config('const.editmode.create'))
                {{Form::open(['method'=>'post', 'id'=>'edit_form', 'route' => 'client.kintai.update'])}}
                @else
                {{Form::open(['method'=>'post', 'id'=>'edit_form', 'route' => ['client.kintai.update', $kintai->id] ])}}
                @endif
                    <input type="hidden" name="mode" id="mode" value="{{ $mode }}">



                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">日付</label>
                            <div class="input-group col-lg-3 col-md-5 col-sm-6">
                                <input type="text" class="form-control" name="day" id="day" value="{{ old('day', ($kintai->day != null ? $kintai->day->format('Y/m/d') : '') ) }}">
                                <span class="input-group-append">
                                    <button type="button" class="btn btn-info btn-flat day_today_btn" >今日</button>
                                </span>
                            </div>
                            @if ($errors->has('day'))
                            <code>{{ $errors->first('day') }}</code>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="code">社員</label>
                            <select name="employee" id="employee" class="form-control select2" style="width: 100%;">
                                <option value="" data-sub-search="">氏名・ふりがなで絞込</option>
@foreach($employees as $employee)
                                <option value="{{$employee->id}}" data-sub-search="{{$employee->kana}}"
                                @if (old('employee_id', $kintai->employee_id) == $employee->id) selected @endif
                                >{{$employee->name}}</option>
@endforeach
                            </select>
                            @if ($errors->has('employee_id'))
                            <code>{{ $errors->first('employee_id') }}</code>
                            @endif
                        </div>
                    </div>
                </div>



@foreach ($dakoku_names as $dakokukey => $dakoku_name)
@php
    // 可変変数->format がエラーになってしまうので
    // Carbonを使って先に定義しておいて 使う時にフォーマット
    $dt = new \Carbon\Carbon($kintai->{"time_{$dakokukey}"});
@endphp
                <div class="card card-{{ config('const.dakokunames_themes.' . $dakokukey) }}">
                    <div class="card-header">
                        <h3 class="card-title">{{ $dakoku_name }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="row">
                                <div class="input-group col-4">
                                    <label for="time_{{ $dakokukey }}">時間</label>
                                </div>
                                <div class="input-group col-4">
                                    <label for="lat_{{ $dakokukey }}">緯度</label>
                                </div>
                                <div class="input-group col-4">
                                    <label for="lon_{{ $dakokukey }}">経度</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="input-group col-4">
                                    <input type="text" class="form-control" name="time_{{ $dakokukey }}" id="time_{{ $dakokukey }}" placeholder="0930" maxlength="4"
                                    value="{{ old("time_{$dakokukey}", $dt->format('Hi') ) }}">
                                    @if ($errors->has("time_{$dakokukey}"))
                                    <code>{{ $errors->first("time_{$dakokukey}") }}</code>
                                    @endif
                                </div>
                                <div class="input-group col-4">
                                    <input type="text" class="form-control" name="lat_{{ $dakokukey }}" id="lat_{{ $dakokukey }}" placeholder="" maxlength="20" value="{{ old("lat_{$dakokukey}", $kintai->{"lat_{$dakokukey}"}) }}">
                                    @if ($errors->has("lat_{$dakokukey}"))
                                    <code>{{ $errors->first("lat_{$dakokukey}") }}</code>
                                    @endif
                                </div>
                                <div class="input-group col-4">
                                    <input type="text" class="form-control" name="lon_{{ $dakokukey }}" id="lon_{{ $dakokukey }}" placeholder="" maxlength="20" value="{{ old("lon_{$dakokukey}", $kintai->{"lon_{$dakokukey}"}) }}">
                                    @if ($errors->has("lon_{$dakokukey}"))
                                    <code>{{ $errors->first("lon_{$dakokukey}") }}</code>
                                    @endif
                                </div>
                            </div>

                        </div>
                        <div class="form-group">
                            <code>9時半の場合「0930」と4桁の数字で入力してください。</code>
                        </div>

                        <div class="form-group">
                            <button type="submit" id="to_map_btn" class="btn btn-primary">Googleマップで確認</button>
                        </div>

                        <div class="form-group">
                            <label for="memo_1">メモ</label>
                            <textarea class="form-control" name="memo_{{ $dakokukey }}" id="memo_{{ $dakokukey }}" placeholder="" maxlength="500" rows="4">{{ old("memo_{$dakokukey}", $kintai->memo_1) }}</textarea>
                            @if ($errors->has("memo_{$dakokukey}"))
                            <code>{{ $errors->first("memo_{$dakokukey}") }}</code>
                            @endif
                        </div>
                    </div>
                </div>
@endforeach



                <div class="card card-primary">
                    <div class="card-footer">
                        <button type="submit" id="commit_btn" class="btn btn-primary">登録</button>
                        <button type="button" id="" class="btn btn-default back_btn float-right" onclick="location.href='{{ route('client.kintai.index') }}'">戻る</button>
                    </div>
                {{ Form::close() }}
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
<script src="{{ asset( cacheBusting('js/client/kintai.js') ) }}"></script>
</script>

<@stop
