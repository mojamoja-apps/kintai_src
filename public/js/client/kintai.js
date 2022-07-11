$(function(){

    // 一覧画面 カレンダー
    $('#day_st, #day_ed').datepicker({
        //showButtonPanel: true,
    });

    // カレンダー 新規モードのみ
    if ($("#mode").val() == _MODE_CREATE) {
        $('#day').datepicker({
            //showButtonPanel: true,
        });
    }

    // 作業日 今日をセットボタン
    $(".day_today_btn").click(function() {
        var now = new Date();
        var yyyymmdd = now.getFullYear() + '/' + ( "0"+( now.getMonth()+1 ) ).slice(-2) + '/' + ( "0"+now.getDate() ).slice(-2);
        $(this).parent().prevAll("input[type='text']").eq(0).val(yyyymmdd);
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


    if ($("#mode").val() == _MODE_CREATE) {
        $(".select2").select2({
            // 上で作った oneSearch メソッドを指定
            matcher: oneSearch
        });

        $("#twoItemSearch").select2({
            // 上で作った twoSearch メソッドを指定
            matcher: twoSearch
        });
    } else {
        // 編集モード時 従業員は変更できない
        // 選択SELECT以外は選択不可にする
        $('select[readonly] option').each(function(index) {
            if ($(this).prop("selected") == false) {
                $(this).prop("disabled", true);
            }
        });
    }



    $('#edit_form').validate({
        rules: {
            day: {
                required: true,
                date: true,
                remote: {
                    url:"/client/api/validate/kintai",
                    type:"post",
                    data: {
                        _token: function() {
                            return CSRF_TOKEN;
                        },
                        client_id: function() {
                            return $('#client_id').val();
                        },
                        employee_id: function() {
                            return $('#employee_id').val();
                        },
                        id: function() {
                            return $('#id').val();
                        },
                    }
                }
            },
            client_id: {
                required: true,
            },
            employee_id: {
                required: true,
            },
        },
        messages: {
            day: {
                required: "必須項目です。",
                date: "有効な日付を入力して下さい。",
                remote: "この日付の勤怠は登録されています。",
            },
            client_id: {
                required: "必須項目です。",
            },
            employee_id: {
                required: "必須項目です。",
            },
        },
        errorElement: 'span',
        errorPlacement: function (error, element) {
            error.addClass('invalid-feedback');
            element.closest('.form-group').append(error);
        },
        highlight: function (element, errorClass, validClass) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
        },
        submitHandler: function (form) {
            Swal.fire({
                title: '保存します。<br>よろしいでしょうか?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'OK',
            }).then((result) => {
                if (result.isConfirmed) {
                    // submitボタン無効 二重送信防止
                    $("#commit_btn").prop("disabled", true);
                    form.submit();
                } else {
                    return false;
                }
            });
        }
    });

})


// Googleマップを開く
function fn_open_map(lat, lon) {

    if (lat !== null && lon !== null
        && lat !== '' && lon !== '') {
        url = 'https://www.google.co.jp/maps/?q=' + lat + ',' + lon + '';
        window.open(url, '_blank');
    }
}



// 一覧での削除ボタン action先を指定
function deleteData(param) {
    $('#delete_form').attr('action', param);
    $('#delete_form').submit();
}
$('#delete_form').validate({
    submitHandler: function (form) {
        Swal.fire({
            title: '削除します。<br>よろしいでしょうか?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'OK',
        }).then((result) => {
            if (result.isConfirmed) {
                // ボタン無効 二重送信防止
                $(".delete_btn").prop("disabled", true);
                form.submit();
            } else {
                return false;
            }
        });
    }
});



