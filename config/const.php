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




];
