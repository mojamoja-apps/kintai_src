// 管理画面・社員スクリプト

$('#edit_form').validate({
    rules: {
        code: {
            alphanum: true,
        },
        name: {
            required: true,
        },
        kana: {
            required: true,
        },
    },
    messages: {
        code: {
            max: "最大8桁で入力してください。",
        },
        name: {
            required: "必須項目です。",
        },
        kana: {
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
                // ボタン無効 二重送信防止
                $("#commit_btn").prop("disabled", true);
                form.submit();
            } else {
                return false;
            }
        });
    }
});


// 半角英数チェック
$.validator.addMethod("alphanum", function(value, element, param) {
    return this.optional(element) || /[a-zA-Z0-9_]/.test(value);
}, '半角英数字で入力してください。');


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

