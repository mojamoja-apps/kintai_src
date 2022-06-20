<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Auth;

class EmployeeController extends Controller
{
    public $search_session_name;

    function __construct() {
        $this->search_session_name = 'employee';
    }

    // 一覧
    public function index(Request $request) {
        // cardの開閉 全閉じ状態を初期値 必要に応じてオープン
        $collapse = config('const.COLLAPSE.CLOSE');

        $query = Employee::query();

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
                    ->OrWhere('kana', 'like', '%'.$value.'%')
                    ->OrWhere('memo', 'like', '%'.$value.'%')
                ;
            }
            $open = true;
        }

        $employees = $query->orderBy('order', 'desc')->orderBy('updated_at', 'desc')->orderBy('id', 'desc')->get();


        if ($open) {
            $collapse = config('const.COLLAPSE.OPEN');
        }

        return view('client/employee/index', compact('employees', 'search', 'collapse'));
    }

    // 登録・編集
    public function edit($id = null) {
        if ($id == null) {
            $mode = config('const.editmode.create');
            $employee = New Employee; //新規なので空のインスタンスを渡す
        } else {
            $mode = config('const.editmode.edit');
            $employee = Employee::find($id);
        }

        return view('client/employee/edit', compact('employee', 'mode'));
    }

    // 更新処理
    public function update(Request $request, $id = null) {
        $request->validate([
            'name' => 'required|max:100',
            'kana' => 'required|max:100',
            'memo' => 'max:5000',
        ]
        ,[
            'name.required' => '氏名は必須項目です。',
            'kana.required' => 'かなは必須項目です。',
            'memo.max' => 'メモは5000文字以下で入力してください。',
        ]);

        // 更新対象データ
        $is_enabled = 0;
        if ($request->input('is_enabled')) {
            $is_enabled = 1;
        }
        $updarr = [
            'name' => $request->input('name'),
            'kana' => $request->input('kana'),
            'memo' => $request->input('memo'),
            'client_id' => Auth::id(),
            'is_enabled' => $is_enabled,
        ];

        Employee::updateOrCreate(
            ['id' => $id],
            $updarr,
        );

        // CSRFトークンを再生成して、二重送信対策
        $request->session()->regenerateToken();

        return redirect( route('client.employee.index') );
    }

    public function destroy(Request $request, $id) {
        $employee = Employee::find($id);
        $employee->delete();

        // CSRFトークンを再生成して、二重送信対策
        $request->session()->regenerateToken();

        return redirect( route('client.employee.index') );
    }
}
