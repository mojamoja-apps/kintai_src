<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class AdminClientController extends Controller
{
    public $search_session_name;

    function __construct() {
        $this->search_session_name = 'client';

        // 作業証明書入力画面では 管理画面レイアウトを調整する
        config(['adminlte.title' => '運営管理画面']);
        config(['adminlte.logo' => '運営管理画面']);
        // ユーザーメニュー非表示
        config(['adminlte.user_menu' => false]);
        // メニュー上書き
        config(['adminlte.menu' =>
            [
                ['header' => '業務'],
                [
                    'text' => '企業管理',
                    'url'  => 'admin/client',
                    'icon' => 'fas fa-fw fa-building',
                ],
            ]
        ]);
    }

    // 一覧
    public function index(Request $request) {
        // cardの開閉 全閉じ状態を初期値 必要に応じてオープン
        $collapse = config('const.COLLAPSE.CLOSE');



        $query = Client::query();

        //検索
        $method = $request->method();
        $session = $request->session()->get($this->search_session_name);

        $search = [];
        $search_keys = [
            'keyword',
        ];
        foreach ($search_keys as $keyname) {
            $search[$keyname] = '';
            if($method == "POST"){
                $search[$keyname] = $request->input($keyname) ? $request->input($keyname) : '';
            } else if($method == "GET") {
                $search[$keyname] = isset($session[$keyname]) ? $session[$keyname] : '';
            }
        }

        // セッションを一旦消して検索値を保存
        $request->session()->forget($this->search_session_name);
        $puts = [];
        foreach ($search_keys as $keyname) {
            $puts[$keyname] = $search[$keyname];
        }
        $request->session()->put($this->search_session_name, $puts);


        $open = false; // 検索ボックスを開くか

        if ($search['keyword']) {
            // 全角スペースを半角に変換
            $spaceConversion = mb_convert_kana($search['keyword'], 's');
            // 単語を半角スペースで区切り、配列にする（例："山田 翔" → ["山田", "翔"]）
            $wordArraySearched = preg_split('/[\s,]+/', $spaceConversion, -1, PREG_SPLIT_NO_EMPTY);
            // 単語をループで回し、ユーザーネームと部分一致するものがあれば、$queryとして保持される
            foreach($wordArraySearched as $value) {
                $query->where('name', 'like', '%'.$value.'%')
                    ->OrWhere('zip', 'like', '%'.$value.'%')
                    ->OrWhere('pref', 'like', '%'.$value.'%')
                    ->OrWhere('address1', 'like', '%'.$value.'%')
                    ->OrWhere('address2', 'like', '%'.$value.'%')
                    ->OrWhere('tel', 'like', '%'.$value.'%')
                    ->OrWhere('memo', 'like', '%'.$value.'%')
                ;
            }
            $open = true;
        }

        if ($open) {
            $collapse = config('const.COLLAPSE.OPEN');
        }

        $companies = $query->orderBy('updated_at', 'desc')->get();


        return view('admin/client/index', compact('companies', 'search', 'collapse'));
    }

    // 登録・編集
    public function edit($id = null) {
        if ($id == null) {
            $mode = config('const.editmode.create');
            $client = New Client; //新規なので空のインスタンスを渡す
        } else {
            $mode = config('const.editmode.edit');
            $client = Client::find($id);
        }
        return view('admin/client/edit', compact('client', 'mode'));
    }

    // 更新処理
    public function update(Request $request, $id = null) {
        $request->validate([
            'name' => 'required|max:100',
        ]
        ,[
            'name.required' => 'お名前は必須項目です。',
        ]);

        // 更新対象データ
        $updarr = [
            'name' => $request->input('name'),
            'zip' => $request->input('zip'),
            'pref' => $request->input('pref'),
            'address1' => $request->input('address1'),
            'address2' => $request->input('address2'),
            'tel' => $request->input('tel'),
            'memo' => $request->input('memo'),
        ];

        Client::updateOrCreate(
            ['id' => $id],
            $updarr,
        );

        // CSRFトークンを再生成して、二重送信対策
        $request->session()->regenerateToken();

        return redirect( route('admin.client.index') );
    }

    public function destroy(Request $request, $id) {
        $client = Client::find($id);
        $client->delete();

        // CSRFトークンを再生成して、二重送信対策
        $request->session()->regenerateToken();

        return redirect( route('admin.client.index') );
    }
}
