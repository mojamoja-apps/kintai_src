<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\Company;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public $search_session_name;
    public $companies;

    function __construct() {
        $this->search_session_name = 'site';

        // 元請け一覧
        $companies = Company::all()->sortBy('id');
        // key,value ペアに直す
        $this->companies = $companies->pluck('name','id')->prepend( "選択してください", "");
    }

    // 一覧
    public function index(Request $request) {
        // cardの開閉 全閉じ状態を初期値 必要に応じてオープン
        $collapse = config('const.COLLAPSE.CLOSE');

        $query = Site::query();

        //検索
        $method = $request->method();
        $session = $request->session()->get($this->search_session_name);

        $search = [];
        $search_keys = [
            'company_id',
            'keyword',
            'is_done',
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

        if ($search['company_id']) {
            $query->where('company_id', $search['company_id']);
            $open = true;
        }

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
                    ->OrWhere('memo', 'like', '%'.$value.'%')
                ;
            }
            $open = true;
        }

        if ($search['is_done']) {
            if ($search['is_done'] == 1) {
                $query->where('is_done', true);
            } else if ($search['is_done'] == 2) {
                $query->where('is_done', false);
            }
            $open = true;
        }


        if ($open) {
            $collapse = config('const.COLLAPSE.OPEN');
        }

        $sites = $query->orderBy('updated_at', 'desc')->get();

        $companies = $this->companies;
        return view('admin/site/index', compact('sites', 'search', 'companies', 'collapse'));
    }

    // 登録・編集
    public function edit($id = null) {
        if ($id == null) {
            $mode = config('const.editmode.create');
            $site = New Site; //新規なので空のインスタンスを渡す
        } else {
            $mode = config('const.editmode.edit');
            $site = Site::find($id);
        }

        $companies = $this->companies;
        return view('admin/site/edit', compact('site', 'companies', 'mode'));
    }

    // 更新処理
    public function update(Request $request, $id = null) {
        $request->validate([
            'company_id' => 'required',
            'name' => 'required|max:100',
            'period_st' => 'nullable|date',
            'period_ed' => 'nullable|date',
            'zip' => 'max:100',
            'pref' => 'max:100',
            'address1' => 'max:100',
            'address2' => 'max:100',
            'memo' => 'max:5000',
        ]
        ,[
            'company_id.required' => '元請けの選択は必須項目です。',
            'name.required' => 'お名前は必須項目です。',
            'period_st.date' => '工期開始には有効な日付を指定してください。',
            'period_ed.date' => '工期終了には有効な日付を指定してください。',
            'memo.max' => 'メモは5000文字以下で入力してください。',
        ]);

        // 更新対象データ
        $is_done = 0;
        if ($request->input('is_done')) {
            $is_done = 1;
        }
        $updarr = [
            'company_id' => $request->input('company_id'),
            'name' => $request->input('name'),
            'period_st' => $request->input('period_st'),
            'period_ed' => $request->input('period_ed'),
            'is_done' => $is_done,
            'zip' => $request->input('zip'),
            'pref' => $request->input('pref'),
            'address1' => $request->input('address1'),
            'address2' => $request->input('address2'),
            'tel' => $request->input('tel'),
            'memo' => $request->input('memo'),
        ];

        Site::updateOrCreate(
            ['id' => $id],
            $updarr,
        );

        // CSRFトークンを再生成して、二重送信対策
        $request->session()->regenerateToken();

        return redirect( route('admin.site.index') );
    }

    public function destroy(Request $request, $id) {
        $site = Site::find($id);
        $site->delete();

        // CSRFトークンを再生成して、二重送信対策
        $request->session()->regenerateToken();

        return redirect( route('admin.site.index') );
    }
}
