// 管理画面・企業スクリプト

// 郵便番号からハイフンを削除
$('#zip').blur(function(e) {
    $(this).val(
        $(this).val().replace(/-/g, '')
    );
})



$('#edit_form').validate({
    rules: {
        name: {
            required: true,
        },
        email: {
            required: true,
            email: true,
        },
        password: {
            // 新規時は必須
            required: $("#mode").val() == _MODE_CREATE,
            minlength: 8,
        },
        hash: {
            required: true,
            minlength: 8,
            maxlength: 32,
        },
        basic_user: {
            required: true,
            maxlength: 10,
        },
        basic_pass: {
            required: true,
            maxlength: 10,
        },
        kinmu_limit_hour: {
            required: $('#zangyo_flg').prop("checked"),
            max: 24,
        },
    },
    messages: {
        name: {
            required: "必須項目です。",
        },
        email: {
            required: "必須項目です。",
            email: "メールアドレスを入力してください。",
        },
        password: {
            required: "必須項目です。",
            minlength: "8文字以上で入力してください。"
        },
        hash: {
            required: "必須項目です。",
            minlength: "8文字以上で入力してください。",
            maxlength: "32文字以下で入力してください。",
        },
        basic_user: {
            required: "必須項目です。",
            maxlength: "8文字以下で入力してください。",
        },
        basic_pass: {
            required: "必須項目です。",
            maxlength: "8文字以下で入力してください。",
        },
        kinmu_limit_hour: {
            required: "残業時間計算の場合、入力してください。",
            max: "24以下で入力してください。",
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

$('#zangyo_flg').change(function (e) {
    if ($(this).prop("checked")) {
        $('.kinmu_limit_hour_box').show('slow');
    } else {
        $('.kinmu_limit_hour_box').hide('slow');
    }
});




$(function(){
    $('#zangyo_flg').change();
})
