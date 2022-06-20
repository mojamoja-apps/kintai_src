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

