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
