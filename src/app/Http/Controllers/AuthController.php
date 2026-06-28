<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // 会員登録画面の表示
    public function showRegister()
    {
        return view('auth.register'); // ※ビューの保存場所に合わせて変更してください
    }

    // 会員登録処理
    public function register(Request $request)
    {
        // 1. バリデーション
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'], // confirmed で password_confirmation と一致するかチェック
        ], [
            // エラーメッセージの日本語カスタマイズ（必要に応じて調整してください）
            'name.required' => 'ユーザー名を入力してください。',
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => '正しいメールアドレスの形式で入力してください。',
            'email.unique' => 'このメールアドレスは既に登録されています。',
            'password.required' => 'パスワードを入力してください。',
            'password.min' => 'パスワードは8文字以上で入力してください。',
            'password.confirmed' => 'パスワードが一致しません。',
        ]);

        // 2. ユーザー作成
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // パスワードは必ずハッシュ化
        ]);

        // 3. 自動ログインして勤怠登録画面へリダイレクト
        Auth::login($user);

        return redirect()->route('attendance.register');
    }

    // ログイン画面の表示
    public function showLogin()
    {
        return view('auth.login'); // ※ビューの保存場所に合わせて変更してください
    }

    // ログイン処理
    public function login(Request $request)
    {
        // 1. バリデーション
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => '正しいメールアドレスの形式で入力してください。',
            'password.required' => 'パスワードを入力してください。',
        ]);

        // 2. ログイン認証の試行
        if (Auth::attempt($credentials)) {
            // セッションの再生成（セキュリティ対策）
            $request->session()->regenerate();

            // 認証成功：勤怠登録画面へ
            return redirect()->route('attendance.register');
        }

        // 3. 認証失敗：メールアドレスの欄にエラーメッセージを戻す
        return back()->withErrors([
            'email' => 'ログイン情報が正しくありません。',
        ])->withInput($request->only('email'));
    }

    // ログアウト処理
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
