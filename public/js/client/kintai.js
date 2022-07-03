$(function(){

$('#day_st, #day_ed').datepicker({
    //showButtonPanel: true,
});

// 作業日 今日をセットボタン
$(".day_today_btn").click(function() {
    var now = new Date();
    var yyyymmdd = now.getFullYear() + '/' + ( "0"+( now.getMonth()+1 ) ).slice(-2) + '/' + ( "0"+now.getDate() ).slice(-2);
    $(this).parent().prevAll("input[type='text']").eq(0).val(yyyymmdd);
});

})





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

