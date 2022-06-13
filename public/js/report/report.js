$(function(){

$('#day, #day_st, #day_ed').datepicker({
    //showButtonPanel: true,
});

$("input[type='number']").inputSpinner({
    groupClass: 'input-group-sm',
    buttonsWidth: "1.5rem"
})


$("#sign").jSignature({
    width:500,
    height:250,
    lineWidth: 5,
});


img = $('#sign_img_url').val();
if (img !== undefined && img != '') {
    toBase64Url(img, function(base64Url){
        $("#sign").jSignature('importData', base64Url);
    });
}


$("#sign_del_btn").click(function() {
    Swal.fire({
        title: 'サイン画像をリセットします。<br>よろしいでしょうか?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'OK',
    }).then((result) => {
        if (result.isConfirmed) {
            $("#sign").jSignature("reset");
        } else {
            return false;
        }
    });
});

// 作業日 今日をセットボタン
$(".day_today_btn").click(function() {
    var now = new Date();
    var yyyymmdd = now.getFullYear() + '/' + ( "0"+( now.getMonth()+1 ) ).slice(-2) + '/' + ( "0"+now.getDate() ).slice(-2);
    $(this).parent().prevAll("input[type='text']").eq(0).val(yyyymmdd);
});


// 単語挿入ボタン
$(".word_btn").click(function() {
    var target_id = '#' + $(this).data('target');
    var text = $(this).data('text') + ' ';
    insertAtCaret(target_id, text);return
});


// 鳶土工追加ボタン
$(".tobidoko_open").click(function() {
    $(this).hide();
    cls = $(this).data('target');
    $("." + cls).show('slow');
});

// 作業員追加ボタン
$("#worker_add_btn").click(function() {
    $(this).hide();
    $(".worker_row").show('slow');
});

// 定時ボタン
$(".teiji_btn").click(function() {
    $(this).prevAll('select.st').eq(0).val("08:00:00");
    $(this).prevAll('select.ed').eq(0).val("17:00:00");
});












$('#edit_form').validate({
    rules: {
        company_id: {
            required: true,
        },
        site_id: {
            required: true,
        },
        day: {
            required: true,
            date: true,
            remote: {
                url:"/api/validate/report",
                type:"post",
                data: {
                    _token: function() {
                        return CSRF_TOKEN;
                    },
                    company_id: function() {
                        return $('#company_id').val();
                    },
                    site_id: function() {
                        return $('#site_id').val();
                    },
                    id: function() {
                        return $('#id').val();
                    },
                }
            }
        },
        // 後でeachで追加しないとうまくいかない
        // "worker_id[]": {
        //     worker_check: true,
        // },
        // "tobidoko[]": {
        //     workertobidoko_check: true,
        // },
    },
    messages: {
        company_id: {
            required: "必須項目です。",
        },
        site_id: {
            required: "必須項目です。",
        },
        day: {
            required: "必須項目です。",
            date: "有効な日付を入力して下さい。",
            remote: "この日付の作業証明書は登録されています。",
        },
        memo: {
            maxlength: "5000文字以内で入力して下さい。",
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
                GetCanvasContents();
                form.submit();
            } else {
                return false;
            }
        });
    }
});

$("select[name^=worker_id]").each(function () {
    $(this).rules("add", {
        worker_check: true,
    });
});
$("[name^=tobidoko]").each(function () {
    $(this).rules("add", {
        workertobidoko_check: true,
    });
});
$("select[name^=driver_id]").each(function () {
    $(this).rules("add", {
        driver_check: true,
    });
});


// 作業員の重複チェック
$.validator.addMethod("worker_check", function(value, element, param) {
    // お決まりの定型文
    // 検証対象の要素にこのルールが設定されているか
    if ( this.optional( element ) ) {
        return true;
    }

    vals = [];
    $("select[name^=worker_id]").each(function(i, elem) {
        if ($(elem).attr('name') == $(element).attr('name')) return true;    // 自分は除いて考える
        if ($(elem).val() != '') {
            vals.push( $(elem).val() );
        }
    });
    if (vals.indexOf(value) !== -1) {
        // 重複あり
        return false;
    } else {
        return true;
    }

}, '作業員の重複があります。');

// 作業員 鳶・土工必須
$.validator.addMethod("workertobidoko_check", function(value, element, param) {
    if ( this.optional( element ) ) {
        // 作業員が選択されていれば 必須選択
        no = $(element).data('tobidoko-no');
        if ($('[data-worker-no="' + no + '"]').val() != '') {
            return false;
        } else {
            return true;
        }
    } else {
        return true;
    }
}, '作業員を選択した場合、鳶・土工を選択してください。');

// 運転手の重複チェック
$.validator.addMethod("driver_check", function(value, element, param) {
    // お決まりの定型文
    // 検証対象の要素にこのルールが設定されているか
    if ( this.optional( element ) ) {
        return true;
    }

    vals = [];
    $("select[name^=driver_id]").each(function(i, elem) {
        if ($(elem).attr('name') == $(element).attr('name')) return true;    // 自分は除いて考える
        if ($(elem).val() != '') {
            vals.push( $(elem).val() );
        }
    });
    if (vals.indexOf(value) !== -1) {
        // 重複あり
        return false;
    } else {
        return true;
    }

}, '運転手の重複があります。');





// サイン画像を保存
// base64化してhiddenにセット
function GetCanvasContents() {
    var base64 = $("#sign").jSignature("getData")
    $('#sign_base64').val(base64);
}

})





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

