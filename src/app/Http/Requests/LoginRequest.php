<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * 誰でもこのリクエストを使えるように true に変更
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * ログイン用のバリデーションルール
     */
    public function rules(): array
    {
        return [
            'email'    => ['required'],
            'password' => ['required'],
        ];
    }

    /**
     * ログイン用のエラーメッセージ
     */
    public function messages(): array
    {
        return [
            'email.required'    => 'メールアドレスを入力してください。',

            'password.required' => 'パスワードを入力してください。',
        ];
    }
}
