<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use Illuminate\Http\Request;

class WorkerController extends Controller
{
    public $search_session_name;
    public $style;
    public $belongs;
    public $insurance;

    function __construct() {
        $this->search_session_name = 'worker';

        // 雇用形態
        $this->style = config('const.style');
        array_unshift($this->style, '選択してください');
        // 所属
        $this->belongs = config('const.belongs');
        array_unshift($this->belongs, '選択してください');
        // 社会保険
        $this->insurance = config('const.insurance');
        array_unshift($this->insurance, '選択してください');
    }

    // 一覧
    public function index(Request $request) {
        // cardの開閉 全閉じ状態を初期値 必要に応じてオープン
        $collapse = config('const.COLLAPSE.CLOSE');

        $query = Worker::query();

        //検索
        $method = $request->method();
        $session = $request->session()->get($this->search_session_name);

        $search = [];
        $search_keys = [
            'belongs',
            'style',
            'insurance',
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

        if ($search['belongs']) {
            $query->where('belongs', $search['belongs']);
            $open = true;
        }
        if ($search['style']) {
            $query->where('style', $search['style']);
            $open = true;
        }
        if ($search['insurance']) {
            $query->where('insurance', $search['insurance']);
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
                    ->OrWhere('memo', 'like', '%'.$value.'%')
                ;
            }
            $open = true;
        }

        $workers = $query->orderBy('updated_at', 'desc')->get();


        if ($open) {
            $collapse = config('const.COLLAPSE.OPEN');
        }

        $style = $this->style;
        $belongs = $this->belongs;
        $insurance = $this->insurance;
        return view('admin/worker/index', compact('workers', 'search', 'style', 'belongs', 'insurance', 'collapse'));
    }

    // 登録・編集
    public function edit($id = null) {
        if ($id == null) {
            $mode = config('const.editmode.create');
            $worker = New Worker; //新規なので空のインスタンスを渡す
        } else {
            $mode = config('const.editmode.edit');
            $worker = Worker::find($id);
        }

        $belongs = $this->belongs;
        return view('admin/worker/edit', compact('worker', 'mode', 'belongs'));
    }

    // 更新処理
    public function update(Request $request, $id = null) {
        $request->validate([
            'name' => 'required|max:100',
            'kana' => 'required|max:100',
            'belongs' => 'required',
            'style' => 'required',
            'insurance' => 'required',
            'memo' => 'max:5000',
        ]
        ,[
            'name.required' => '氏名は必須項目です。',
            'kana.required' => 'かなは必須項目です。',
            'belongs.required' => '所属の選択は必須項目です。',
            'style.required' => '雇用形態の選択は必須項目です。',
            'insurance.required' => '社会保険の選択は必須項目です。',
            'memo.max' => 'メモは5000文字以下で入力してください。',
        ]);

        // 更新対象データ
        $updarr = [
            'name' => $request->input('name'),
            'kana' => $request->input('kana'),
            'belongs' => $request->input('belongs'),
            'style' => $request->input('style'),
            'insurance' => $request->input('insurance'),
            'memo' => $request->input('memo'),
        ];

        Worker::updateOrCreate(
            ['id' => $id],
            $updarr,
        );

        // CSRFトークンを再生成して、二重送信対策
        $request->session()->regenerateToken();

        return redirect( route('admin.worker.index') );
    }

    public function destroy(Request $request, $id) {
        $worker = Worker::find($id);
        $worker->delete();

        // CSRFトークンを再生成して、二重送信対策
        $request->session()->regenerateToken();

        return redirect( route('admin.worker.index') );
    }
}
