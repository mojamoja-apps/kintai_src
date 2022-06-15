@extends('adminlte::page')

@section('title', '作業員編集')

@section('content_header')
    <h1>作業員編集</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-6">
            <div class="card card-primary">

                @if ($mode == config('const.editmode.create'))
                {{Form::open(['method'=>'post', 'id'=>'edit_form', 'route' => 'admin.worker.update'])}}
                @else
                {{Form::open(['method'=>'post', 'id'=>'edit_form', 'route' => ['admin.worker.update', $worker->id] ])}}
                @endif
                    <input type="hidden" name="mode" id="mode" value="{{ $mode }}">



                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">氏名</label>
                            <input type="text" class="form-control" name="name" id="name" placeholder="山田 太郎" value="{{ old('name', $worker->name) }}">
                            @if ($errors->has('name'))
                            <code>{{ $errors->first('name') }}</code>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="kana">かな</label>
                            <input type="text" class="form-control" name="kana" id="kana" placeholder="やまだ たろう" value="{{ old('kana', $worker->kana) }}">
                            @if ($errors->has('kana'))
                            <code>{{ $errors->first('kana') }}</code>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="belongs">所属</label>
                            {{ Form::select('belongs', $belongs, old('belongs', $worker->belongs), ['class' => 'form-control']) }}
                            @if ($errors->has('belongs'))
                            <code>{{ $errors->first('belongs') }}</code>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="belongs">雇用形態</label>
                            <div class="form-inline">
                                @foreach (config('const.style') as $key => $item)
                                <div class="custom-control custom-radio">
                                    <input class="custom-control-input" type="radio" name="style" id="style_{{$key}}" value="{{$key}}"
                                    @if ((int)old('style') == $key) checked
                                    @elseif ($worker->style == $key) checked
                                    @elseif ($key == 1 && $worker->style == null) checked
                                    @endif
                                    >
                                    <label for="style_{{$key}}" class="custom-control-label">{{$item}}　</label>
                                </div>
                                @endforeach
                            </div>
                            @if ($errors->has('style'))
                            <code>{{ $errors->first('style') }}</code>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="belongs">社会保険</label>
                            <div class="form-inline">
                                @foreach (config('const.insurance') as $key => $item)
                                <div class="custom-control custom-radio">
                                    <input class="custom-control-input" type="radio" name="insurance" id="insurance_{{$key}}" value="{{$key}}"
                                    @if ((int)old('insurance') == $key) checked
                                    @elseif ($worker->insurance == $key) checked
                                    @elseif ($key == 1 && $worker->insurance == null) checked
                                    @endif
                                    >
                                    <label for="insurance_{{$key}}" class="custom-control-label">{{$item}}　</label>
                                </div>
                                @endforeach
                            </div>
                            @if ($errors->has('insurance'))
                            <code>{{ $errors->first('insurance') }}</code>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="memo">メモ</label>
                            <textarea class="form-control" name="memo" id="memo" placeholder="" maxlength="4999" rows="8">{{ old('memo', $worker->memo) }}</textarea>
                            @if ($errors->has('memo'))
                            <code>{{ $errors->first('memo') }}</code>
                            @endif
                        </div>
                    </div>



                    <div class="card-footer">
                        <button type="submit" id="commit_btn" class="btn btn-primary">登録</button>
                        <button type="button" id="" class="btn btn-default back_btn float-right" onclick="location.href='{{ route('admin.worker.index') }}'">戻る</button>
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
<script src="{{ asset( cacheBusting('js/admin/worker.js') ) }}"></script>
</script>

<@stop
