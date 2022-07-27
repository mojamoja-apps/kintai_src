<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Support\Facades\Hash;
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
                ['header' => 'マスター設定'],
                [
                    'text' => '企業管理',
                    'url'  => 'admin/client',
                    'icon' => 'fas fa-fw fa-building',
                ],
            ]
        ]);
    }


    // ダッシュボード
    public function dashbord() {
        return view('admin/index');
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
                    ->OrWhere('email', 'like', '%'.$value.'%')
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

            //新規時にはハッシュを自動生成する
            $client->hash = md5(uniqid(mt_rand(), true));
        } else {
            $mode = config('const.editmode.edit');
            $client = Client::find($id);
        }
        return view('admin/client/edit', compact('client', 'mode'));
    }

    // 更新処理
    public function update(Request $request, $id = null) {
        if ($request->input('mode') == config('const.editmode.create')) {
            $request->validate([
                'name' => 'required',
                'email' => 'required|email|max:100|unique:clients,email',
                'password' => 'required|min:8|max:50',
                'hash' => 'required|min:8|max:32',
                'basic_user' => 'required|max:10',
                'basic_pass' => 'required|max:10',
            ]
            ,[
                'name.required' => '必須項目です。',
                'email.required' => '必須項目です。',
                'email.unique' => 'このログインIDは登録されています。',
                'password.required' => '必須項目です。',
                'password.min' => 'パスワードは8文字以上で入力してください。',
                'hash.required' => '必須項目です。',
                'hash.min' => 'URL用コードは8文字以上で入力してください。',
                'hash.max' => 'URL用コードは32文字以下で入力してください。',
                'basic_user.required' => '必須項目です。',
                'basic_user.max' => '10文字以下で入力してください。',
                'basic_pass.required' => '必須項目です。',
                'basic_pass.max' => '10文字以下で入力してください。',
            ]);
        } else {
            $request->validate([
                'name' => 'required|max:100',
                'email' => 'required|email|max:100|unique:clients,email,' . $id . ',id',
                'password' => 'nullable|min:8|max:50',
                'hash' => 'required|min:8|max:32',
                'basic_user' => 'required|max:10',
                'basic_pass' => 'required|max:10',
            ]
            ,[
                'name.required' => '会社名は必須項目です。',
                'email.required' => 'ログインIDは必須項目です。',
                'email.unique' => 'このログインIDは登録されています。',
                'hash.required' => 'URL用コードは必須項目です。',
                'hash.min' => 'URL用コードは8文字以上で入力してください。',
                'hash.max' => 'URL用コードは32文字以下で入力してください。',
                'basic_user.required' => '必須項目です。',
                'basic_user.max' => '10文字以下で入力してください。',
                'basic_pass.required' => '必須項目です。',
                'basic_pass.max' => '10文字以下で入力してください。',
            ]);
        }

        // 更新対象データ

        $is_enabled = 0;
        if ($request->input('is_enabled')) {
            $is_enabled = 1;
        }
        $gps = 0;
        if ($request->input('gps')) {
            $gps = 1;
        }
        $midnight = 0;
        if ($request->input('midnight')) {
            $midnight = 1;
        }
        $smile_csv = 0;
        if ($request->input('smile_csv')) {
            $smile_csv = 1;
        }
        $updarr = [
            'name' => $request->input('name'),
            'is_enabled' => $is_enabled,
            'email' => $request->input('email'),
            'zip' => $request->input('zip'),
            'pref' => $request->input('pref'),
            'address1' => $request->input('address1'),
            'address2' => $request->input('address2'),
            'tel' => $request->input('tel'),
            'memo' => $request->input('memo'),
            'hash' => $request->input('hash'),
            'basic_user' => $request->input('basic_user'),
            'basic_pass' => $request->input('basic_pass'),
            'gps' => $gps,
            'rest' => $request->input('rest'),
            'midnight' => $midnight,
            'zangyo_flg' => $request->input('zangyo_flg') ?? 0,
            'kinmu_limit_hour' => $request->input('kinmu_limit_hour') ?? 0,
            'smile_csv' => $smile_csv,
        ];
        // パスワードの入力がある場合は更新対象に含める
        if ($request->input('password') !== null) {
            $updarr['password'] = Hash::make( $request->input('password') );
        }

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
