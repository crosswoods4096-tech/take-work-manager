<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CorrectionRequest extends FormRequest
{
    /**
     * このリクエストの実行を許可するか
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 💡 1. バリデーションルール本体
     */
    public function rules(): array
    {
        return [
            'check_in'  => ['required', 'date_format:H:i'],
            // ① 退勤が出勤より後であることをチェック（after:check_in）
            'check_out' => ['required', 'date_format:H:i', 'after:check_in'],

            // ④ 備考欄（理由）の必須チェック
            'remarks'   => ['required', 'string', 'max:1000'],

            'rests'     => ['nullable', 'array'],
            // ② 休憩開始は「出勤より後（after:check_in）」かつ「退勤より前（before:check_out）」
            'rests.*.start_time' => [
                'required_with:rests.*.end_time',
                'date_format:H:i',
                'after:check_in',
                'before:check_out'
            ],
            // ②&③ 休憩終了は「休憩開始より後（after:rests.*.start_time）」かつ「退勤より前（before:check_out）」
            'rests.*.end_time'   => [
                'required_with:rests.*.start_time',
                'date_format:H:i',
                'after:rests.*.start_time',
                'before:check_out'
            ],
        ];
    }

    /**
     * 💡 2. ご指定いただいたエラーメッセージへの差し替え
     */
    public function messages(): array
    {
        return [
            'check_in.required'  => '出勤時間を入力してください。',
            'check_out.required' => '退勤時間を入力してください。',

            // ① 退勤時刻が出勤時刻より前（または同時）の場合
            'check_out.after'    => '出勤時間もしくは退勤時間が不適切な値です。',

            // ④ 備考欄が未入力の場合
            'remarks.required'   => '備考を記入してください。',

            // ② 休憩開始が出勤前、または退勤後にある場合
            'rests.*.start_time.after'  => '休憩時間が不適切な値です。',
            'rests.*.start_time.before' => '休憩時間が不適切な値です。',

            // ③ 休憩終了が退勤より後にある場合（※開始より前の場合も同じメッセージでカバー）
            'rests.*.end_time.after'    => '休憩時間が不適切な値です。',
            'rests.*.end_time.before'   => '休憩時間もしくは退勤時間が不適切な値です。',
            // 💡 【追記】新しい追加枠（new）が空のままエラー判定に引っかかった時の日本語メッセージ
            'rests.new.start_time.date_format' => '休憩時間が不適切な値です。',
            'rests.new.start_time.after'       => '休憩時間が不適切な値です。',
            'rests.new.start_time.before'      => '休憩時間が不適切な値です。',
            'rests.new.end_time.date_format'   => '休憩時間が不適切な値です。',
            'rests.new.end_time.after'         => '休憩時間が不適切な値です。',
            'rests.new.end_time.before'        => '休憩時間もしくは退勤時間が不適切な値です。',
        ];
    }
}
