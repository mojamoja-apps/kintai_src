@extends('adminlte::page')

@section('title', '勤怠打刻修正')

@section('content_header')
    <h1>勤怠打刻修正</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-6">
            @if ($mode == config('const.editmode.create'))
            {{Form::open(['method'=>'post', 'id'=>'edit_form', 'route' => 'client.kintai.update'])}}
            @else
            {{Form::open(['method'=>'post', 'id'=>'edit_form', 'route' => ['client.kintai.update', $kintai->id] ])}}
            @endif
                <div class="card card-primary">
                    <input type="hidden" name="mode" id="mode" value="{{ $mode }}">
                    <input type="hidden" name="client_id" id="client_id" value="{{ Auth::id() }}">
                    <input type="hidden" name="id" id="id" value="{{ $kintai->id }}">



                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">日付</label>
                            <div class="input-group col-lg-4 col-md-5 col-sm-6">
                                <input type="text" class="form-control" name="day" id="day" value="{{ old('day', ($kintai->day != null ? $kintai->day->format('Y/m/d') : '') ) }}"
                                @if ($mode == config('const.editmode.edit'))
                                readonly
                                @endif
                                >

                                @if ($mode == config('const.editmode.create'))
                                <span class="input-group-append">
                                    <button type="button" class="btn btn-info btn-flat day_today_btn" >今日</button>
                                </span>
                                @endif
                            </div>
                            @if ($errors->has('day'))
                            <code>{{ $errors->first('day') }}</code>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="code">社員</label>
                            <select name="employee_id" id="employee_id" class="form-control select2"
                            @if ($mode == config('const.editmode.edit'))
                            readonly
                            @endif
                            >
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
    if ($kintai->{"time_{$dakokukey}"} !== NULL) {
        $dt = new \Carbon\Carbon($kintai->{"time_{$dakokukey}"});
        $disptime = $dt->format('Hi');
    } else {
        $disptime = NULL;
    }
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
                                @if (Auth::user()->gps == true)
                                <div class="input-group col-4">
                                    <label for="lat_{{ $dakokukey }}">緯度</label>
                                </div>
                                <div class="input-group col-4">
                                    <label for="lon_{{ $dakokukey }}">経度</label>
                                </div>
                                @endif
                            </div>
                            <div class="row">
                                <div class="input-group col-4">
                                    <input type="text" class="form-control" name="time_{{ $dakokukey }}" id="time_{{ $dakokukey }}" placeholder="0930" maxlength="4"
                                    value="{{ old("time_{$dakokukey}", $disptime ) }}">
                                    @if ($errors->has("time_{$dakokukey}"))
                                    <code>{{ $errors->first("time_{$dakokukey}") }}</code>
                                    @endif
                                </div>
                                @if (Auth::user()->gps == true)
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
                                @endif
                            </div>

                        </div>


                        @if ($dakokukey == config('const.dakokumode.syukkin'))
                        {{-- 初回のみ表示 --}}
                        <div class="form-group">
                            <code>9時半の場合「0930」と4桁の数字で入力してください。</code>
                        </div>
                        @endif


                        @if ($dakokukey == config('const.dakokumode.taikin'))
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="midnight" name="midnight" value="1"
                                    @if ((int)old('midnight') == 1) checked
                                    @elseif ($kintai->midnight == 1) checked
                                    {{-- @elseif ($mode == config('const.editmode.create')) checked --}}
                                    @endif
                                >
                                    <label class="custom-control-label" for="midnight">深夜残業の場合チェックの上、2時半退勤なら「0230」と入力してください。</label>
                            </div>
                        </div>
                        @endif

                        @if (Auth::user()->gps == true)
                        <div class="form-group">
                            <button type="button" id="" class="to_map_btn btn btn-primary" onclick="fn_open_map($('#lat_{{ $dakokukey }}').val(), $('#lon_{{ $dakokukey }}').val());">Googleマップで確認</button>
                        </div>
                        @endif

                        <div class="form-group">
                            <label for="memo_{{ $dakokukey }}">メモ</label>
                            <textarea class="form-control" name="memo_{{ $dakokukey }}" id="memo_{{ $dakokukey }}" placeholder="" maxlength="500" rows="4">{{ old("memo_{$dakokukey}", $kintai->{"memo_{$dakokukey}"}) }}</textarea>
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
                </div>
            {{ Form::close() }}
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
<script src="{{ asset( cacheBusting('js/client/kintai.js') ) }}"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1/i18n/jquery.ui.datepicker-ja.min.js"></script>


@stop
