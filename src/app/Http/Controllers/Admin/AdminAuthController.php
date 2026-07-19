<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AdminLoginRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    // ログイン画面の表示
    public function showLoginForm()
    {
        // すでに管理者としてログイン済みの場合は日次一覧へ
        if (Auth::check() && Auth::user()->role === 'admin') {
            return redirect()->route('admin.attendance.daily');
        }
        return view('admin.auth.login');
    }

    // ログイン処理
    public function login(AdminLoginRequest $request)
    {
        // 1. この時点で、メール・パスワードの「未入力チェック」は自動で通過しています。

        // 2. 送信されたログイン情報の取得
        $credentials = $request->only('email', 'password');

        // 管理者としてのログイン試行（roleがadminであることも条件に加えるのが安全です）
        // ※ credentialsに加えて、roleのチェックも一緒に行う場合の例です
        $credentials['role'] = 'admin';

        if (Auth::attempt($credentials)) {
            // ⭕️ ログイン成功
            $request->session()->regenerate();
            return redirect()->route('admin.attendance.daily'); // 管理者トップへ
        }

        // ❌ ログイン情報が間違っている場合（要件のメッセージを設定）
        return back()->withErrors([
            'login_error' => 'ログイン情報が登録されていません',
        ])->withInput($request->only('email')); // メールアドレスだけ入力値を保持
    }

    // ログアウト処理
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'ログアウトしました。');
    }
}
