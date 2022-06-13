@extends('adminlte::page')

@section('title', '作業証明書入力')

@section('content_header')
    <h1>作業証明書詳細</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            @if ($mode == config('const.editmode.create'))
            {{Form::open(['method'=>'post', 'files' => true, 'id'=>'edit_form', 'route' => 'report.update'])}}
            @else
            {{Form::open(['method'=>'post', 'files' => true, 'id'=>'edit_form', 'route' => ['report.update', $report->id] ])}}
            @endif
                <input type="hidden" name="mode" value="{{ $mode }}">
                <input type="hidden" name="id" id="id" value="{{ $report->id }}">

                <div class="card card-default">
                    <div class="card-header">
                        <h3 class="card-title">基本情報</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group col-lg-12">
                            <label for="name">元請け</label>
                            <input type="text" class="form-control" name="" id="" value="{{ $report->company->name }}" readonly>
                        </div>

                        <div class="form-group col-lg-12">
                            <label for="name">作業所</label>
                            <input type="text" class="form-control" name="" id="" value="{{ $report->site->name }}" readonly>
                        </div>

                        <div class="form-group col-lg-3 col-md-5 col-sm-6">
                            <label for="name">作業日</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="day" id="day" value="{{ old('day', ($report->day != null ? $report->day->format('Y/m/d') : '') ) }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>


@foreach(config('const.KOJINAMES') as $kojikey => $kojiname)
                <div class="card card-default {{$collapse[$kojikey]['CARD_CLASS']}}">
                    <div class="card-header">
                        <h3 class="card-title">{{ $kojiname }}</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse" data-animation-speed="300">
                                <i class="fas {{$collapse[$kojikey]['BTN_CLASS']}}"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body" style="{{$collapse[$kojikey]['BODY_STYLE']}}">
                        @if ($kojikey == config('const.KOJI.KOJI_JOYO1') || $kojikey == config('const.KOJI.KOJI_JOYO2'))
                        <div class="form-group col-md-6">
                            {{ Form::select("koji_{$kojikey}_kbn", config('const.KOJI.KOJI_KBN_LIST_NAME'), old("koji_{$kojikey}_kbn", $report->{"koji_" . $kojikey . "_kbn"}), ['class' => 'form-control st', 'disabled' => 'disabled']) }}
                        </div>
                        @endif

                        <div class="form-group col-lg-12">
                            <label for="name">作業内容</label>
                            <textarea class="form-control" name="koji_{{$kojikey}}_memo" id="koji_{{$kojikey}}_memo" rows="8" placeholder="" readonly>{{ old("koji_{$kojikey}_memo", $report->{"koji_" . $kojikey . "_memo"}) }}</textarea>
                        </div>



                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-1 text-center">
                                    <label class="small" for="xxx">　</label>
                                </div>
                                <div class="col-md-6 text-center">
                                    <label class="small" for="xxx">稼働時間</label>
                                </div>
                                <div class="col-md-3 text-center">
                                    <label class="small" for="xxx">員数</label>
                                </div>
                                <div class="col-md-2 text-center">
                                    <label class="small" for="xxx">早残</label>
                                </div>
                            </div>
                            @foreach(config('const.TOBI_DOKO') as $tobidokokey => $tobidokoname)
                            @for ($ix = 1; $ix <= 5; $ix++)
                            @if (
                                empty($report->{"koji_" . $kojikey . "_" . $tobidokokey . "_" . $ix . "_sttime"})
                                && empty($report->{"koji_" . $kojikey . "_" . $tobidokokey . "_" . $ix . "_edtime"})
                                && empty($report->{"koji_" . $kojikey . "_" . $tobidokokey . "_" . $ix . "_num"})
                                && empty($report->{"koji_" . $kojikey . "_" . $tobidokokey . "_" . $ix . "_sozan"})
                            ) @continue
                            @endif
                            <div class="row mb-1">
                                <div class="col-md-1 text-right">
                                    <label class="small" for="xxx">{{$tobidokoname}}</label>
                                </div>
                                <div class="col-md-6 form-inline input-group-sm">
                                    {{ Form::select("koji_{$kojikey}_{$tobidokokey}_{$ix}_sttime", config('const.KADOTIMES'), old("koji_{$kojikey}_{$tobidokokey}_{$ix}_sttime", $report->{"koji_" . $kojikey . "_" . $tobidokokey . "_" . $ix . "_sttime"}), ['class' => 'form-control st', 'disabled' => 'disabled']) }}
                                    <span class="m-1">～</span>
                                    {{ Form::select("koji_{$kojikey}_{$tobidokokey}_{$ix}_edtime", config('const.KADOTIMES'), old("koji_{$kojikey}_{$tobidokokey}_{$ix}_edtime", $report->{"koji_" . $kojikey . "_" . $tobidokokey . "_" . $ix . "_edtime"}), ['class' => 'form-control ed', 'disabled' => 'disabled']) }}
                                </div>
                                <div class="col-md-3 input-group-sm">
                                    <input type="number" class="form-control" name="koji_{{$kojikey}}_{{$tobidokokey}}_{{$ix}}_num" id="koji_{{$kojikey}}_{{$tobidokokey}}_{{$ix}}_num" min="0" max="100" value="{{ old("koji_{$kojikey}_{$tobidokokey}_{$ix}_num", $report->{"koji_" . $kojikey . "_" . $tobidokokey . "_" . $ix . "_num"}) }}" disabled>
                                </div>
                                <div class="col-md-2 input-group-sm">
                                    {{ Form::select("koji_{$kojikey}_{$tobidokokey}_{$ix}_sozan", config('const.SOZAN'), old("koji_{$kojikey}_{$tobidokokey}_{$ix}_sozan", $report->{"koji_" . $kojikey . "_" . $tobidokokey . "_" . $ix . "_sozan"}), ['class' => 'form-control', 'disabled' => 'disabled']) }}
                                </div>
                            </div>
                            @endfor
                            @endforeach
                        </div>

                        @if ($kojikey == config('const.KOJI.KOJI_CONCRETE'))
                        <div class="form-group">
                            <div class="row mb-1">
                                <div class="col-md-1 text-right">
                                    <label class="small" for="xxx">総打設数量(㎡)</label>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control" name="koji_{{$kojikey}}_dasetu" id="koji_{{$kojikey}}_dasetu" min="0" max="1000" value="{{ old("koji_{$kojikey}_dasetu", $report->{"koji_" . $kojikey . "_dasetu"}) }}" disabled>
                                </div>
                            </div>
                        </div>
                        @endif


                        @if ($kojikey == config('const.KOJI.KOJI_CONCRETE') || $kojikey == config('const.KOJI.KOJI_DOKO'))
                        {{-- 重機ダンプポンプは　コンクリート、土工事のみ --}}
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-1 text-center">
                                    <label class="small" for="xxx">　</label>
                                </div>
                                <div class="col-md-5 text-center">
                                    <label class="small" for="xxx">稼働時間</label>
                                </div>
                                <div class="col-md-2 text-center">
                                    <label class="small" for="xxx">種類</label>
                                </div>
                                <div class="col-md-2 text-center">
                                    <label class="small" for="xxx">台数</label>
                                </div>
                                <div class="col-md-2 text-center">
                                    <label class="small" for="xxx">早残</label>
                                </div>
                            </div>
                            @for ($ix = 1; $ix <= 5; $ix++)
                            @if ($kojikey == config('const.KOJI.KOJI_CONCRETE') && $ix >= 3)
                            @break
                            @endif
                            <div class="row mb-1">
                                <div class="col-md-1 text-right">
                                    <label class="small" for="xxx">
                                    @if ($kojikey == config('const.KOJI.KOJI_CONCRETE'))
                                    ポンプ
                                    @elseif ($ix <= 3)
                                    重機
                                    @else
                                    ダンプ
                                    @endif
                                    </label>
                                </div>
                                <div class="col-md-5 form-inline input-group-sm">
                                    {{ Form::select("koji_{$kojikey}_car_{$ix}_sttime", config('const.KADOTIMES'), old("koji_{$kojikey}_car_{$ix}_sttime", $report->{"koji_" . $kojikey . "_car_" . $ix . "_sttime"}), ['class' => 'form-control st', 'disabled' => 'disabled']) }}
                                    <span class="m-1">～</span>
                                    {{ Form::select("koji_{$kojikey}_car_{$ix}_edtime", config('const.KADOTIMES'), old("koji_{$kojikey}_car_{$ix}_edtime", $report->{"koji_" . $kojikey . "_car_" . $ix . "_edtime"}), ['class' => 'form-control ed', 'disabled' => 'disabled']) }}
                                </div>
                                <div class="col-md-2 input-group-sm input-group-sm">
                                    @php
                                        $datasource = [];
                                        if ($kojikey == config('const.KOJI.KOJI_CONCRETE')) {
                                            // ポンプ
                                            $datasource = config('const.PUMP');
                                        } else if ($ix <= 3) {
                                            // 重機
                                            $datasource = config('const.JUKI');
                                        } else {
                                            // ダンプ
                                            $datasource = config('const.DUMP');
                                        }
                                    @endphp

                                    {{ Form::select("koji_{$kojikey}_car_{$ix}_ton", $datasource, old("koji_{$kojikey}_car_{$ix}_ton", $report->{"koji_" . $kojikey . "_car_" . $ix . "_ton"}), ['class' => 'form-control', 'disabled' => 'disabled']) }}
                                </div>
                                <div class="col-md-2 input-group-sm">
                                    <input type="number" class="form-control no-padding" name="koji_{{$kojikey}}_car_{{$ix}}_num" id="koji_{{$kojikey}}_car_{{$ix}}_num" min="0" max="100" value="{{ old("koji_{$kojikey}_car_{$ix}_num", $report->{"koji_" . $kojikey . "_car_" . $ix . "_num"}) }}" disabled>
                                </div>
                                <div class="col-md-2 input-group-sm">
                                    {{ Form::select("koji_{$kojikey}_car_{$ix}_sozan", config('const.SOZAN'), old("koji_{$kojikey}_car_{$ix}_sozan", $report->{"koji_" . $kojikey . "_car_" . $ix . "_sozan"}), ['class' => 'form-control', 'disabled' => 'disabled']) }}
                                </div>
                            </div>
                            @endfor
                        </div>
                        @endif
                    </div>
                </div>
@endforeach


















                <div class="card card-default">
                    <div class="card-header">
                        <h3 class="card-title">作業員</h3>
                    </div>

                    <div class="card-body">
                        <div class="form-group">
                            @php
                            $worker_cnt = -1;
                            @endphp
                            @for ($ix = 0; $ix < 10; $ix++)
                                <div class="row mb-1 worker_row"
                                @if ($ix >= 5 && $collapse[98] == false)
                                    style="display:none;"
                                @endif
                                >
                                    @for ($iy = 0; $iy < 3; $iy++)
                                    @php
                                    $worker_cnt++;
                                    @endphp
                                    <div class="col-2 input-group-sm">
                                        {{ Form::select("worker_id[{$worker_cnt}]"
                                        , $workers
                                        , old("workers", isset($report->reportworkings[$worker_cnt]->worker_id) ? $report->reportworkings[$worker_cnt]->worker_id : '')
                                        , ['class' => 'form-control no-padding', 'data-worker-no' => $worker_cnt, 'disabled' => 'disabled']) }}
                                    </div>
                                    <div class="col-1 input-group-sm">
                                        {{ Form::select("tobidoko[{$worker_cnt}]"
                                        , config('const.TOBI_DOKO_KBN_SHORT')
                                        , old("workers", isset($report->reportworkings[$worker_cnt]->tobidoko) ? $report->reportworkings[$worker_cnt]->tobidoko : '')
                                        , ['class' => 'form-control no-padding', 'data-tobidoko-no' => $worker_cnt, 'disabled' => 'disabled']) }}
                                    </div>
                                    <div class="col-1 input-group-sm">
                                        {{ Form::select("sozan[{$worker_cnt}]"
                                        , config('const.SOZAN')
                                        , old("workers", isset($report->reportworkings[$worker_cnt]->sozan) ? $report->reportworkings[$worker_cnt]->sozan : '')
                                        , ['class' => 'form-control no-padding', 'data-sozan-no' => $worker_cnt, 'disabled' => 'disabled']) }}
                                    </div>
                                    @endfor
                                </div>
                            @endfor
                        </div>



                        <div class="form-group col-lg-12 mt-5">
                            <label for="name">運転者</label>
                        </div>



                        <div class="form-group">
                            <div class="row mb-1">
                                @php
                                $driver_cnt = -1;
                                @endphp
                                @for ($iy = 0; $iy < 3; $iy++)
                                    @php
                                    $driver_cnt++;
                                    @endphp
                                <div class="col-3 input-group-sm">
                                    {{ Form::select("driver_id[{$iy}]"
                                        , $workers
                                        , old("workers", isset($report->reportdrivers[$driver_cnt]->worker_id) ? $report->reportdrivers[$driver_cnt]->worker_id : '')
                                        , ['class' => 'form-control', 'disabled' => 'disabled']) }}
                                </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>






                <div class="card card-default {{$collapse[99]['CARD_CLASS']}}">
                    <div class="card-header">
                        <h3 class="card-title">応援</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool collapse_close" data-card-widget="collapse" data-animation-speed="300">
                                <i class="fas {{$collapse[99]['BTN_CLASS']}}"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body" style="{{$collapse[99]['BODY_STYLE']}}">
                        <div class="form-group">
                            @for ($ix = 0; $ix < 10; $ix++)
                                <div class="row mb-1 worker_row">
                                    @for ($iy = 0; $iy < 3; $iy++)
                                    @php
                                    $worker_cnt++;
                                    @endphp
                                    <div class="col-2 input-group-sm">
                                        {{ Form::select("worker_id[{$worker_cnt}]"
                                        , $workers
                                        , old("workers", isset($report->reportworkings[$worker_cnt]->worker_id) ? $report->reportworkings[$worker_cnt]->worker_id : '')
                                        , ['class' => 'form-control no-padding', 'data-worker-no' => $worker_cnt, 'disabled' => 'disabled']) }}
                                    </div>
                                    <div class="col-1 input-group-sm">
                                        {{ Form::select("tobidoko[{$worker_cnt}]"
                                        , config('const.TOBI_DOKO_KBN_SHORT')
                                        , old("workers", isset($report->reportworkings[$worker_cnt]->tobidoko) ? $report->reportworkings[$worker_cnt]->tobidoko : '')
                                        , ['class' => 'form-control no-padding', 'data-tobidoko-no' => $worker_cnt, 'disabled' => 'disabled']) }}
                                    </div>
                                    <div class="col-1 input-group-sm">
                                        {{ Form::select("sozan[{$worker_cnt}]"
                                        , config('const.SOZAN')
                                        , old("workers", isset($report->reportworkings[$worker_cnt]->sozan) ? $report->reportworkings[$worker_cnt]->sozan : '')
                                        , ['class' => 'form-control no-padding', 'data-sozan-no' => $worker_cnt, 'disabled' => 'disabled']) }}
                                    </div>
                                    @endfor
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>


                <div class="card card-default">
                    <div class="card-header">
                        <h3 class="card-title">認証者サイン</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group col-lg-12">
                            <img src="{{ $report->id != null ? asset( cacheBusting('storage/sign/' . $report->id . '.png') ) : '' }}" class="border">
                        </div>
                    </div>
                </div>

                <div class="card card-default">
                    <div class="card-footer">
                        <button type="button" id="" class="btn btn-default back_btn float-right" onclick="location.href='{{ route('admin.report.index') }}'">戻る</button>
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
<script src="{{ asset( cacheBusting('js/admin/report.js') ) }}"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jSignature/2.1.3/jSignature.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1/i18n/jquery.ui.datepicker-ja.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-input-spinner@1.9.7/src/bootstrap-input-spinner.js"></script>


<script>

$(function(){

})
</script>

@stop
