<?php
// データベースユーザーを使わない簡易basic認証

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BasicAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // envで設定オフfalseになっていればやらない
        // ユーザー一覧は/config/basicauth.php
        if (config('basicauth.active') == false) {
            return $next($request);
        }

        $username = $request->getUser();
        $password = $request->getPassword();

        $auth_ok = false;
        foreach (config('basicauth.users') as $user) {
            if (
                $username == $user['id']
                && $password == $user['pass']
            ) {
                $auth_ok = true;
                break;
            }
        }

        if ($auth_ok) {
            return $next($request);
        }

        abort(401, "Enter username and password.", [
            header('WWW-Authenticate: Basic realm="auth area"'),
            header('Content-Type: text/plain; charset=utf-8')
        ]);
    }
}
