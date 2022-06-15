@extends('adminlte::page')

@section('title', '管理者編集')

@section('content_header')
    <h1>管理者編集</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-6">
            <div class="card card-primary">

                @if ($mode == config('const.editmode.create'))
                {{Form::open(['method'=>'post', 'id'=>'edit_form', 'route' => 'admin.user.update'])}}
                @else
                {{Form::open(['method'=>'post', 'id'=>'edit_form', 'route' => ['admin.user.update', $user->id] ])}}
                @endif
                    <input type="hidden" name="mode" id="mode" value="{{ $mode }}">



                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">お名前</label>
                            <input type="text" class="form-control" name="name" id="name" placeholder="山田 太郎" value="{{ old('name', $user->name) }}">
                            @if ($errors->has('name'))
                            <code>{{ $errors->first('name') }}</code>
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="email">ログインID</label>
                            <input type="text" class="form-control" name="email" id="email" placeholder="yamada" value="{{ old('email', $user->email) }}">
                            @if ($errors->has('email'))
                            <code>{{ $errors->first('email') }}</code>
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="password">パスワード</label>
                            <input type="password" class="form-control" name="password" id="password" placeholder="Password" value="">
                            @if ($mode == config('const.editmode.edit'))
                            <p><code>変更する時のみ入力してください。</code></p>
                            @endif
                            @if ($errors->has('password'))
                            <code>{{ $errors->first('password') }}</code>
                            @endif
                        </div>
                    </div>



                    <div class="card-footer">
                        <button type="submit" id="commit_btn" class="btn btn-primary">登録</button>
                        <button type="button" id="" class="btn btn-default back_btn float-right" onclick="location.href='{{ route('admin.user.index') }}'">戻る</button>
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
<script src="{{ asset( cacheBusting('js/admin/user.js') ) }}"></script>
@stop
