<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // 一覧
    public function index() {
        $users = User::all()->sortByDesc('updated_at');
        return view('admin/user/index', compact('users'));
    }

    // 登録・編集
    public function edit($id = null) {
        if ($id == null) {
            $mode = config('const.editmode.create');
            $user = New User; //新規なので空のインスタンスを渡す
        } else {
            $mode = config('const.editmode.edit');
            $user = User::find($id);
        }
        return view('admin/user/edit', compact('user', 'mode'));
    }

    // 更新処理
    public function update(Request $request, $id = null) {
        if ($request->input('mode') == config('const.editmode.create')) {
            $request->validate([
                'name' => 'required',
                'email' => 'required|email|max:100|unique:users,email',
                'password' => 'required|min:8|max:50',
            ]
            ,[
                'name.required' => 'お名前は必須項目です。',
                'email.required' => 'ログインIDは必須項目です。',
                'email.unique' => 'このログインIDは登録されています。',
                'password.required' => 'パスワードは必須項目です。',
                'password.min' => 'パスワードは8文字以上で入力してください。',
            ]);
        } else {
            $request->validate([
                'name' => 'required|max:100',
                'email' => 'required|email|max:100|unique:users,email,' . $id . ',id',
                'password' => 'nullable|min:8|max:50',
            ]
            ,[
                'name.required' => 'お名前は必須項目です。',
                'email.required' => 'ログインIDは必須項目です。',
                'email.unique' => 'このログインIDは登録されています。',
            ]);
        }

        // $user = User::find($id);

        // $user->name = $request->input('name');
        // $user->email = $request->input('email');
        // $user->password = Hash::make( $request->input('password') );
        // $user->save();


        // 更新対象データ
        $updarr = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
        ];
        // パスワードの入力がある場合は更新対象に含める
        if ($request->input('password') !== null) {
            $updarr['password'] = Hash::make( $request->input('password') );
        }

        User::updateOrCreate(
            ['id' => $id],
            $updarr,
        );

        // CSRFトークンを再生成して、二重送信対策
        $request->session()->regenerateToken();

        return redirect( route('admin.user.index') );
    }

    public function destroy(Request $request, $id) {
        $user = User::find($id);
        $user->delete();

        // CSRFトークンを再生成して、二重送信対策
        $request->session()->regenerateToken();

        return redirect( route('admin.user.index') );
    }
}
