@extends('adminlte::page')

@section('title', '企業一覧')

@section('content_header')
    <h1>企業一覧</h1>
@stop

@section('content')
<div class="form-group mt-15">
    <button type="button" class="btn btn-primary" onclick="location.href='{{ route('admin.client.edit') }}'">新規登録</button>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            {{Form::open(['method'=>'post', 'id'=>'search_form'])}}
                <div class="card card-default {{$collapse['CARD_CLASS']}}">
                    <div class="card-header">
                        <h3 class="card-title">検索</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool collapse_close" data-card-widget="collapse" data-animation-speed="300">
                                <i class="fas {{$collapse['BTN_CLASS']}}"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body" style="{{$collapse['BODY_STYLE']}}">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>フリーワード検索</label>
                                    <input type="search" class="form-control" placeholder="スペース区切りで複数キーワード" name="keyword" value="{{$search['keyword']}}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button type="button" class="btn btn-secondary float-right ml-1" onclick="clearSearchForm();">クリア</button>
                                    <button type="submit" class="btn btn-primary float-right">検索</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            {{ Form::close() }}
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table id="datatable1" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>状態</th>
                                <th>社名</th>
                                <th>メールアドレス</th>
                                <th>登録日時</th>
                                <th>更新日時</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($companies as $client)
                            <tr>
                                <td>{{ $client->id }}</td>
                                <td>@if ($client->is_enabled == 1) <span class="text-primary">有効</span> @else <span class="text-danger">無効</span> @endif</td>
                                <td>
                                    <a href="{{route('kintai.index',['hash' => $client->hash])}}" target="_blank">
                                        {{ $client->name }}
                                    </a>
                                </td>
                                <td>{{ $client->email }}</td>
                                <td>{{ $client->created_at }}</td>
                                <td>{{ $client->updated_at }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-primary" onclick="location.href='{{route('admin.client.edit',['id' => $client->id])}}'">編集</button>
                                    <button type="button" class="btn btn-danger delete_btn" onclick="deleteData('{{ route('admin.client.destroy',['id' => $client->id]) }}');">削除</button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>


{{-- 検索クリアボタン用フォーム --}}
{{Form::open(['method'=>'post', 'id'=>'search_clear_form'])}}
{{ Form::close() }}


{{-- 削除ボタン用フォーム --}}
{{Form::open(['method'=>'post', 'id'=>'delete_form'])}}
{{ Form::close() }}

@stop




@section('css')
<link rel="stylesheet" href="{{ asset( cacheBusting('css/common.css') ) }}">
@stop

@section('js')
<script src="{{ asset( cacheBusting('js/common.js') ) }}"></script>
<script src="{{ asset( cacheBusting('js/admin/client.js') ) }}"></script>
<script>


$('#datatable1').DataTable({
    "language": {
        "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Japanese.json",
    },
    "stateSave": true,
    "paging": true,
    "lengthChange": false,
    "searching": true,
    "ordering": true,
    "info": true,
    "autoWidth": false,
    "responsive": true,
    "columnDefs": [
        { responsivePriority: 1, targets: 0 },
        { responsivePriority: 2, targets: -1 },
        { responsivePriority: 3, targets: 1 },
    ],
});
</script>
@stop
