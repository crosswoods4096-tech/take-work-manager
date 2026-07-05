<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * 1. 誰でもこのリクエストを使えるように true に変更します。
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 2. バリデーションルールを定義します。
     */
    public function rules(): array
    {
        return [
            'name'     => ['required'],
            'email'    => ['required', 'email'],
            // confirmed をつけることで、password_confirmation フィールドとの一致を自動チェックします
            'password' => ['required', 'min:8', 'confirmed'],
        ];
    }

    /**
     * 3. 要件に合わせたエラーメッセージを設定します。
     */
    public function messages(): array
    {
        return [
            // お名前
            'name.required'         => 'お名前を入力してください',

            // メールアドレス
            'email.required'        => 'メールアドレスを入力してください',
            'email.email'           => 'メールアドレスはメール形式で入力してください',

            // パスワード
            'password.required'     => 'パスワードを入力してください',
            'password.min'          => 'パスワードは８文字以上で入力してください',
            'password.confirmed'    => 'パスワードと一致しません',
        ];
    }
}
