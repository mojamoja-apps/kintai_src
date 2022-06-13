// 管理画面・元請け企業スクリプト

// 郵便番号からハイフンを削除
$('#zip').blur(function(e) {
    $(this).val(
        $(this).val().replace(/-/g, '')
    );
})



$('#edit_form').validate({
    rules: {
        company_id: {
            required: true,
        },
        name: {
            required: true,
        },
        period_st: {
            date: true,
        },
        period_ed: {
            date: true,
        },
    },
    messages: {
        company_id: {
            required: "必須項目です。",
        },
        name: {
            required: "必須項目です。",
        },
        period_st: {
            date: "有効な日付を入力して下さい。",
        },
        period_ed: {
            date: "有効な日付を入力して下さい。",
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

