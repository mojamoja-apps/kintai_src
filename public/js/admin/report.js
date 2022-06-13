$(function(){

$('#day_st, #day_ed').datepicker({
    //showButtonPanel: true,
});



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



// 作業日 今日をセットボタン
$(".day_today_btn").click(function() {
    var now = new Date();
    var yyyymmdd = now.getFullYear() + '/' + ( "0"+( now.getMonth()+1 ) ).slice(-2) + '/' + ( "0"+now.getDate() ).slice(-2);
    $(this).parent().prevAll("input[type='text']").eq(0).val(yyyymmdd);
});

})










// PDF出力ボタン action先を指定
function pdfData(param) {
    $('#pdf_form').attr('action', param);
    $('#pdf_form').submit();
}
