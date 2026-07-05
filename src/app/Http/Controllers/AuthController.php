<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * 会員登録画面の表示
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * 会員登録処理
     * 引数を Request から 「RegisterRequest」 に変更します！
     */
    public function register(RegisterRequest $request)
    {
        // ここに到達した時点で、RegisterRequest 内のバリデーション（要件の文言）は通過しています。

        // 1. ユーザー作成 (バリデーション済みのデータを使用)
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password), // パスワードをハッシュ化
        ]);

        // 2. 自動ログインして勤怠登録画面へリダイレクト
        Auth::login($user);

        return redirect()->route('attendance.register');
    }

    /**
     * ログイン画面の表示
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * ログイン処理
     */
    public function login(LoginRequest $request)
    {
        // ここに到達した時点で、すでにLoginRequestのバリデーションは通過しています。

        // 1. 認証に必要なデータ（emailとpassword）のみを取得
        $credentials = $request->only('email', 'password');

        // 2. ログイン認証の試行
        if (Auth::attempt($credentials)) {
            // セッションの再生成（セキュリティ対策）
            $request->session()->regenerate();

            // 認証成功：勤怠登録画面へ
            return redirect()->route('attendance.register');
        }

        // 3. 認証失敗：メールアドレスの欄にエラーメッセージを戻す
        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません。',
        ])->withInput($request->only('email'));
    }

    /**
     * ログアウト処理
     */
    public function logout(Request $request)
    {
        Auth::logout();

        // セッションのクリア
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // ログアウト後はログイン画面へ
        return redirect()->route('login');
    }
}
