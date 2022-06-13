// 共通スクリプト
const _MODE_CREATE = 'create';
const _MODE_EDIT = 'edit';

// CSRFトークン取得
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

function insertAtCaret(target, str) {
    var obj = $(target);
    obj.focus();
    if(navigator.userAgent.match(/MSIE/)) {
        var r = document.selection.createRange();
        r.text = str;
        r.select();
    } else {
        var s = obj.val();
        var p = obj.get(0).selectionStart;
        var np = p + str.length;
        obj.val(s.substr(0, p) + str + s.substr(p));
        obj.get(0).setSelectionRange(np, np);
    }
}

// 検索クリアボタン
function clearSearchForm() {
    $('#search_clear_form').submit();
}



// 画像をURLからbase64に変換
// https://pisuke-code.com/js-way-to-convert-img-to-base64/
function toBase64Url(url, callback){
  var xhr = new XMLHttpRequest();
  xhr.onload = function() {
    var reader = new FileReader();
    reader.onloadend = function() {
      callback(reader.result);
    }
    reader.readAsDataURL(xhr.response);
  };
  xhr.open('GET', url);
  xhr.responseType = 'blob';
  xhr.send();
}


// 配列内で値が重複してないか調べる
// 重複していればtrue
function existsSameValue(a){
  var s = new Set(a);
  return s.size != a.length;
}



