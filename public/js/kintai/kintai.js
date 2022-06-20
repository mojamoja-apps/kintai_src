


var Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000
});

$('.btn').click(function (e) {

    navigator.geolocation.getCurrentPosition(fn_send, fn_error);


            //   console.log(position.coords.latitude);
            // console.log(position.coords.longitude);
});

function fn_send(position) {
    Swal.fire({
        title: '山田太郎 さん<br>'
        + '出勤<br>'
        + '09:30<br>',
        icon: 'question',

        showCancelButton: true,
        confirmButtonText: 'OK',
    }).then((result) => {
        if (result.isConfirmed) {
            // ボタン無効 二重送信防止
            // $("#commit_btn").prop("disabled", true);
            // form.submit();


            // これはテスト
            $('#overlay_spin').show();
            setTimeout(function(){
                $('#overlay_spin').hide();
                Toast.fire({
                    icon: 'success',
                    title: '登録しました！' + position.coords.latitude + position.coords.longitude,
                });
            },1000);
        } else {
            return false;
        }
    });
}


function fn_error() {
     Swal.fire({
        title: '端末の位置情報の送信が許可されていません。',
        text: '端末の位置情報の許可設定を行った上、再読み込みを行ってください。',
        icon: 'error',
    }).then((result) => {
        //
    });
}

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



