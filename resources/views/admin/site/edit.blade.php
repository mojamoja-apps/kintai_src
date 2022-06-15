@extends('adminlte::page')

@section('title', '作業所編集')

@section('content_header')
    <h1>作業所編集</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-6">
            <div class="card card-primary">

                @if ($mode == config('const.editmode.create'))
                {{Form::open(['method'=>'post', 'id'=>'edit_form', 'route' => 'admin.site.update'])}}
                @else
                {{Form::open(['method'=>'post', 'id'=>'edit_form', 'route' => ['admin.site.update', $site->id] ])}}
                @endif
                    <input type="hidden" name="mode" id="mode" value="{{ $mode }}">



                    <div class="card-body">
                        <div class="form-group">
                            <label for="company_id">元請け</label>
                            {{ Form::select('company_id', $companies, old('company_id', $site->company_id), ['class' => 'form-control']) }}
                            @if ($errors->has('company_id'))
                            <code>{{ $errors->first('company_id') }}</code>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="name">作業所名</label>
                            <input type="text" class="form-control" name="name" id="name" placeholder="府中" value="{{ old('name', $site->name) }}">
                            @if ($errors->has('name'))
                            <code>{{ $errors->first('name') }}</code>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="name">工期</label>
                            <div class="form-inline">
                                <input type="text" class="form-control" name="period_st" id="period_st" placeholder="2022/01/01" value="{{ old('period_st', $site->period_st) }}">
                                ～
                                <input type="text" class="form-control" name="period_ed" id="period_ed" placeholder="2022/12/31" value="{{ old('period_ed', $site->period_ed) }}">
                            </div>
                            @if ($errors->has('period_st'))
                            <code>{{ $errors->first('period_st') }}</code>
                            @endif
                            @if ($errors->has('period_ed'))
                            <code>{{ $errors->first('period_ed') }}</code>
                            @endif
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="is_done" id="is_done" value="1"
                                    @if ((int)old('is_done') == 1) checked
                                    @elseif ($site->is_done == 1) checked
                                    @endif
                                >
                                <label for="is_done" class="custom-control-label">作業完了済 (新規作業証明書登録時に一覧に出てこなくなる)</label>
                            </div>
                            @if ($errors->has('is_done'))
                            <code>{{ $errors->first('is_done') }}</code>
                            @endif
                        </div>

                        <div class="form-group h-adr">
                            <span class="p-country-name" style="display:none;">Japan</span>
                            <label for="name">住所</label>
                            <div class="row mb-2">
                                <div class="col-lg-2 col-md-3">
                                    <label class="" for="zip">郵便番号</label>
                                </div>
                                <div class="col-lg-3 col-md-5 col-sm-5">
                                    <input type="text" class="form-control p-postal-code" name="zip" id="zip" placeholder="1000001" maxlength="8" value="{{ old('zip', $site->zip) }}">
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-lg-2 col-md-3">
                                    <label class="" for="pref">都道府県</label>
                                </div>
                                <div class="col-lg-3 col-md-5 col-sm-5">
                                    <input type="text" class="form-control p-region" name="pref" id="pref" placeholder="○○県" maxlength="10" value="{{ old('pref', $site->pref) }}">
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-lg-2 col-md-3">
                                    <label class="" for="address1">市区町村</label>
                                </div>
                                <div class="col-lg-9">
                                    <input type="text" class="form-control p-locality" name="address1" id="address1" placeholder="○○町" maxlength="50" value="{{ old('address1', $site->address1) }}">
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-lg-2 col-md-3">
                                    <label class="" for="address2">町名番地</label>
                                </div>
                                <div class="col-lg-9">
                                    <input type="text" class="form-control p-street-address p-extended-address" name="address2" id="address2" placeholder="○○ 123-45" maxlength="50" value="{{ old('address2', $site->address2) }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="memo">メモ</label>
                            <textarea class="form-control" name="memo" id="memo" placeholder="" maxlength="4999" rows="8">{{ old('memo', $site->memo) }}</textarea>
                            @if ($errors->has('memo'))
                            <code>{{ $errors->first('memo') }}</code>
                            @endif
                        </div>
                    </div>



                    <div class="card-footer">
                        <button type="submit" id="commit_btn" class="btn btn-primary">登録</button>
                        <button type="button" id="" class="btn btn-default back_btn float-right" onclick="location.href='{{ route('admin.site.index') }}'">戻る</button>
                    </div>
                {{ Form::close() }}
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
<script src="{{ asset( cacheBusting('js/admin/site.js') ) }}"></script>
<script src="https://yubinbango.github.io/yubinbango/yubinbango.js" charset="UTF-8"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jSignature/2.1.3/jSignature.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1/i18n/jquery.ui.datepicker-ja.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-input-spinner@1.9.7/src/bootstrap-input-spinner.js"></script>

<script>
$('#period_st').datepicker({
    showButtonPanel: true,
});
$('#period_ed').datepicker({
    showButtonPanel: true,
});
</script>

<@stop
