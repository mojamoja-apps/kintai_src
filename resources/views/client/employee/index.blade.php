@extends('adminlte::page')

@section('title', '従業員一覧')

@section('content_header')
    <h1>従業員一覧</h1>
@stop

@section('content')
<div class="form-group mt-15">
    <button type="button" class="btn btn-primary" onclick="location.href='{{ route('client.employee.edit') }}'">新規登録</button>
    <button type="button" class="btn btn-info" onclick="$('#order_form').submit();">並び順更新</button>
    <code>ドラッグ&ドロップで行を並べ替え後、並び順更新ボタンを押してください。</code>
</div>



<div class="container-fluid">

    <div class="row">
        <div class="col-12">
        {{Form::open(['method'=>'post', 'id'=>'order_form',  'route' => 'client.employee.orderupdate'])}}
            <div class="card">
                <div class="card-body">
                    <table id="datatable1" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>表示順</th>
                                <th>コード</th>
                                <th>状態</th>
                                <th>氏名</th>
                                <th>かな</th>
                                <th>登録日時</th>
                                <th>更新日時</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($employees as $employee)
                            <tr>
                                <td class="text-center">
                                    <i class="fas fa-bars"> {{ $loop->iteration }}</i>
                                    <input type="hidden" name="ids[]" value="{{ $employee->id }}">
                                </td>
                                <td>{{ $employee->code }}</td>
                                <td>@if ($employee->is_enabled == 1) <span class="text-primary">有効</span> @else <span class="text-danger">無効</span> @endif</td>
                                <td>{{ $employee->name }}</td>
                                <td>{{ $employee->kana }}</td>
                                <td>{{ $employee->created_at }}</td>
                                <td>{{ $employee->updated_at }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-primary" onclick="location.href='{{route('client.employee.edit',['id' => $employee->id])}}'">編集</button>
                                    <button type="button" class="btn btn-danger delete_btn" onclick="deleteData('{{ route('client.employee.destroy',['id' => $employee->id]) }}');">削除</button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        {{ Form::close() }}
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="{{ asset( cacheBusting('js/common.js') ) }}"></script>
<script src="{{ asset( cacheBusting('js/client/employee.js') ) }}"></script>
<script>


$( "#datatable1 tbody" ).sortable();
$( "#datatable1 tbody" ).disableSelection();


$('#datatable1').DataTable({
    "language": {
        "url": "//cdn.datatables.net/plug-ins/1.12.1/i18n/ja.json",
    },
    "stateSave": true,
    "paging": false,
    "lengthChange": false,
    "searching": false,
    "ordering": false,
    "info": true,
    "autoWidth": false,
    "responsive": true,
    "columnDefs": [
        { responsivePriority: 1, targets: 1 },
        { responsivePriority: 2, targets: -1 },
        { responsivePriority: 3, targets: 3 },
        { responsivePriority: 4, targets: 4 },
    ],
});


@if (session('flash_message'))
// 表示順更新後のフラッシュメッセージ
var Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000
});
Toast.fire({
  icon: 'success',
  title: '{{ session('flash_message') }}'
})
@endif


</script>
@stop
