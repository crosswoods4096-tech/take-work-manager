<?php

namespace App\Http\Controllers\Admin;

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
    public function login(Request $request)
    {
        // バリデーション
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // 💡 認証を試みる
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // ログイン成功後、さらに role が admin かどうかをチェック
            if (Auth::user()->role === 'admin') {
                $request->session()->regenerate();
                return redirect()->route('admin.attendance.daily');
            }

            // 一般ユーザー（general）だった場合は即座にログアウトさせてエラーにする
            Auth::logout();
            return back()->withErrors([
                'email' => '管理者アカウントではないため、ログインできません。',
            ]);
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません。',
        ]);
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
