<?php
// 共通関数ファイル

// https://yoshinorin.net/articles/2019/12/24/laravel-cachebusting-without-mix/
// LaravelでMixを使わずにcache bustingする
// パラメーターにファイル更新日タイムスタンプを付与する
// このように使用する
// <script src="{{ asset( cacheBusting('js/admin/user.js') ) }}"></script>
function cacheBusting(string $filePath)
{
    if ( app()->isLocal() || app()->runningUnitTests() ) {
        // テスト環境, ローカル環境用ではやらない
    } else if (File::exists($filePath)) {
        $unixTimeStamp = File::lastModified($filePath);
        return "{$filePath}?{$unixTimeStamp}";
    }
    return $filePath;
}


/**
* ベーシック認証をかける
*
* @param array $auth_list ユーザー情報(複数ユーザー可) array("ユーザ名" => "パスワード") の形式
* @param string $realm レルム文字列
* @param string $failed_text 認証失敗時のエラーメッセージ
*/
function fn_basic_auth($auth_list,$realm="Restricted Area",$failed_text="HTTP/1.0 401 Unauthorized"){
	if (isset($_SERVER['PHP_AUTH_USER']) and isset($auth_list[$_SERVER['PHP_AUTH_USER']])){
		if ($auth_list[$_SERVER['PHP_AUTH_USER']] == $_SERVER['PHP_AUTH_PW']){
			return $_SERVER['PHP_AUTH_USER'];
		}
	}

	header('WWW-Authenticate: Basic realm="'.$realm.'"');
	header('HTTP/1.0 401 Unauthorized');
	header('Content-type: text/html; charset='.mb_internal_encoding());

	die($failed_text);
}


/**
* 勤務時間数 0.5単位で切り捨て
  2倍して小数点以下切り捨て、割る2
  8.1→8
  7.9→7.5
  7.4→7
  1.6→1.5
*
* @param number 切り捨て対象数値
* @return number 切り捨て後数値
*/
function reitengo_floor($num) {
    return floor($num * 2) /2;
}

/**
* 休憩時間数 0.5単位で切り上げ
  2倍して小数点以下切り上げ、割る2
  8.1→8.5
  7.9→8
  7.4→7.5
  1.6→2
*
* @param number 切り上げ対象数値
* @return number 切り上げ後数値
*/
function reitengo_ceil($num) {
    return ceil($num * 2) /2;
}

/**
* 勤務時間数 0.25単位で切り捨て
  4倍して小数点以下切り捨て、割る4
  8.1→8
  7.9→7.75
  7.4→7.25
  1.6→1.5
*
* @param number 切り捨て対象数値
* @return number 切り捨て後数値
*/
function reitennigo_floor($num) {
    return floor($num * 4) /4;
}

/**
* 休憩時間数 0.25単位で切り上げ
  4倍して小数点以下切り上げ、割る4
  8.1→8.25
  7.9→8
  7.4→7.5
  1.6→1.75
*
* @param number 切り上げ対象数値
* @return number 切り上げ後数値
*/
function reitennigo_ceil($num) {
    return ceil($num * 4) /4;
}
