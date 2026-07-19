<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminLoginRequest extends FormRequest
{
    /**
     * リクエストの実行を許可するか（true に変更）
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 💡 1. 必須チェックとメール形式の基本ルール
     */
    public function rules(): array
    {
        return [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * 💡 2. 要件通りのエラーメッセージを設定
     */
    public function messages(): array
    {
        return [
            'email.required'    => 'メールアドレスを入力してください',
            'email.email'       => '有効なメールアドレス形式で入力してください',
            'password.required' => 'パスワードを入力してください',
        ];
    }
}
