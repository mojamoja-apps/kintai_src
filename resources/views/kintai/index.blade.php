@extends('adminlte::page')

@section('title', '勤怠入力サンプル')

@section('content_header')

@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default mt-1">
                <div class="card-header">
                    <h3 class="card-title">勤怠入力サンプル</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <select class="form-control select2" style="width: 100%;">
 <option value="" data-sub-search="">氏名・ふりがなで絞込</option>
 <option value="1" data-sub-search="いとうじゅん">伊藤 潤</option>
 <option value="2" data-sub-search="いのうえりょう">井上 亮</option>
 <option value="3" data-sub-search="いとうひでゆき">伊藤 英之</option>
 <option value="4" data-sub-search="まえだしんいち">前田 真一</option>
 <option value="5" data-sub-search="さとうたつや">佐藤 達矢</option>
 <option value="6" data-sub-search="むらまつだいすけ">村松 大輔</option>
 <option value="7" data-sub-search="すずきしん">鈴木 慎</option>
 <option value="8" data-sub-search="いがらしじゅん">五十嵐 淳</option>
 <option value="9" data-sub-search="やまだゆうき">山田 勇輝</option>
<option value="10" data-sub-search="すずきえみ">鈴木 恵美</option>
<option value="11" data-sub-search="たかはしゆか">高橋 由香</option>
<option value="12" data-sub-search="たかはしはるみ">高橋 晴美</option>
<option value="13" data-sub-search="たなかゆみ">田中 有美</option>
<option value="14" data-sub-search="おかもとゆみ">岡本 由美</option>
<option value="15" data-sub-search="あんどうゆみこ">安藤 由美子</option>
<option value="16" data-sub-search="たかぎなおこ">高木 直子</option>
<option value="17" data-sub-search="あんどうひとみ">安藤 仁美</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-12">
                        <button type="submit" id="commit_btn" class="btn btn-block btn-lg btn-primary">出勤</button>
                        <button type="submit" id="commit_btn" class="btn btn-block btn-lg btn-success">休憩入</button>
                        <button type="submit" id="commit_btn" class="btn btn-block btn-lg btn-info">休憩出</button>
                        <button type="submit" id="commit_btn" class="btn btn-block btn-lg btn-danger">退勤</button>
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


<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1/i18n/jquery.ui.datepicker-ja.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-input-spinner@1.9.7/src/bootstrap-input-spinner.js"></script>


<script>

var Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000
});

$('.btn').click(function (e) {
    $('#overlay_spin').show();
    setTimeout(function(){
        $('#overlay_spin').hide();
        Toast.fire({
            icon: 'success',
            title: '登録しました！',
        });
    },1000);
});



var customMatcher = function(params, data, select2SearchStr) {
    var modifiedData;
    if ($.trim(params.term) === '') {
        return data;
    }
    if (typeof data.text === 'undefined') {
        return null;
    }
    if (data.text.indexOf(params.term) > -1) {
        modifiedData = $.extend({}, data, true);
        return modifiedData;
    }

    //
    if (select2SearchStr === null || select2SearchStr === void 0) {
        return null;
    }
    if (select2SearchStr.toString().indexOf(params.term) > -1) {
        modifiedData = $.extend({}, data, true);
        return modifiedData;
    }
    return null;
};

// サブ検索項目1つで検索する
var oneSearch = function(params, data) {
    var item;
    // data属性 sub-search の内容を検索する
    item = $(data.element).data('sub-search');
    return customMatcher(params, data, item);
};

// サブ検索項目複数で検索する
var twoSearch = function(params, data) {
    var item_1, item_2, items;
    // data属性 sub-search の内容を検索する
    item_1 = $(data.element).data('sub-search');
    // data属性 sub-two-search の内容を検索する
    item_2 = $(data.element).data('sub-two-search');

    // 複数項目で検索したい場合は配列を入力する
    items = [item_1, item_2]
    return customMatcher(params, data, items);
};


$(".select2").select2({
    // 上で作った oneSearch メソッドを指定
    matcher: oneSearch
});

$("#twoItemSearch").select2({
    // 上で作った twoSearch メソッドを指定
    matcher: twoSearch
});

</script>

@stop
