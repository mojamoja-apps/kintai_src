@extends('adminlte::page')

@section('title', '社員編集')

@section('content_header')
    <h1>社員編集</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-6">
            <div class="card card-primary">

                @if ($mode == config('const.editmode.create'))
                {{Form::open(['method'=>'post', 'id'=>'edit_form', 'route' => 'client.employee.update'])}}
                @else
                {{Form::open(['method'=>'post', 'id'=>'edit_form', 'route' => ['client.employee.update', $employee->id] ])}}
                @endif
                    <input type="hidden" name="mode" id="mode" value="{{ $mode }}">



                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">氏名</label>
                            <input type="text" class="form-control" name="name" id="name" placeholder="山田 太郎" value="{{ old('name', $employee->name) }}">
                            @if ($errors->has('name'))
                            <code>{{ $errors->first('name') }}</code>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="kana">かな</label>
                            <input type="text" class="form-control" name="kana" id="kana" placeholder="やまだ たろう" value="{{ old('kana', $employee->kana) }}">
                            @if ($errors->has('kana'))
                            <code>{{ $errors->first('kana') }}</code>
                            @endif
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_enabled" name="is_enabled" value="1"
                                    @if ((int)old('is_enabled') == 1) checked
                                    @elseif ($employee->is_enabled == 1) checked
                                    @elseif ($mode == config('const.editmode.create')) checked
                                    @endif
                                >
                                    <label class="custom-control-label" for="is_enabled">有効</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="memo">メモ</label>
                            <textarea class="form-control" name="memo" id="memo" placeholder="" maxlength="4999" rows="8">{{ old('memo', $employee->memo) }}</textarea>
                            @if ($errors->has('memo'))
                            <code>{{ $errors->first('memo') }}</code>
                            @endif
                        </div>
                    </div>



                    <div class="card-footer">
                        <button type="submit" id="commit_btn" class="btn btn-primary">登録</button>
                        <button type="button" id="" class="btn btn-default back_btn float-right" onclick="location.href='{{ route('client.employee.index') }}'">戻る</button>
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
<script src="{{ asset( cacheBusting('js/client/employee.js') ) }}"></script>
</script>

<@stop
