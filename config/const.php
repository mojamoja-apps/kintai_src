<?php
// 手動作成 基本定数ファイル
// \public\js\admin\common.js にも設定あり
return [
    'editmode' => [
        'create' => 'create',
        'edit' => 'edit',
        'destroy' => 'destroy',
    ],

    'dakokumode' => [
        'syukkin' => 1,
        'kyuukei_1_st' => 2,
        'kyuukei_1_ed' => 3,
        'kyuukei_2_st' => 4,
        'kyuukei_3_ed' => 5,
        'taikin' => 6,
    ],

    'dakokunames_rest_1' => [
        1 => '出勤',
        6 => '退勤',
    ],

    'dakokunames_rest_2' => [
        1 => '出勤',
        2 => '休憩開始',
        3 => '休憩終了',
        6 => '退勤',
    ],

    'dakokunames_rest_3' => [
        1 => '出勤',
        2 => '休憩①開始',
        3 => '休憩①終了',
        4 => '休憩②開始',
        5 => '休憩②終了',
        6 => '退勤',
    ],

    'dakokunames_themes' => [
        1 => 'primary',
        2 => 'success',
        3 => 'info',
        4 => 'secondary',
        5 => 'warning',
        6 => 'danger',
    ],


    'youbi' => [
        '日', //0
        '月', //1
        '火', //2
        '水', //3
        '木', //4
        '金', //5
        '土', //6
    ],

    // 作業証明書 1回での取得件数マックス
    'max_get' => 1000,

    // adminlte cardが閉じてる・開いてるのクラス・スタイル
/*
閉じてる
collapsed-card
fa-plus
display: none;


開いてる
なし
fa-minus
display: block;
*/
    'COLLAPSE' => [
        'CLOSE' => [
            'CARD_CLASS' => 'collapsed-card',
            'BTN_CLASS'  => 'fa-plus',
            'BODY_STYLE' => 'display: none;',
        ],
        'OPEN' => [
            'CARD_CLASS' => '',
            'BTN_CLASS'  => 'fa-minus',
            'BODY_STYLE' => '',
        ],
    ],


    'REST' => [
        1 => '休憩なし',
        2 => '休憩1あり',
        3 => '休憩2あり',
    ],


    // プラネットワーク様ID 本番2
    // 専用CSVダウンロード
    'PLANETWORK_IDS' => [
        2
    ],

];
