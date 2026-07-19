<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

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
     * 💡 1. 基本的なフォーマットと必須チェックのみをルールで行う
     */
    public function rules(): array
    {
        return [
            'check_in'  => ['required', 'date_format:H:i'],
            'check_out' => ['required', 'date_format:H:i'],
            'remarks'   => ['sometimes', 'required', 'string', 'max:1000'],
            'rests'     => ['nullable', 'array'],
            'rests.*.start_time' => ['nullable', 'required_with:rests.*.end_time', 'date_format:H:i'],
            'rests.*.end_time'   => ['nullable', 'required_with:rests.*.start_time', 'date_format:H:i'],
        ];
    }

    /**
     * 💡 2. 複雑な時間の前後関係の論理チェックは、ここで確実に判定する
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $checkInStr  = $this->input('check_in');
            $checkOutStr = $this->input('check_out');

            // 出退勤時間のパースを試みる
            if ($checkInStr && $checkOutStr) {
                try {
                    $checkIn  = Carbon::createFromFormat('H:i', $checkInStr);
                    $checkOut = Carbon::createFromFormat('H:i', $checkOutStr);

                    // ① 退勤が出勤より前または同じ場合
                    if (!$checkOut->greaterThan($checkIn)) {
                        $validator->errors()->add('check_out', '出勤時間もしくは退勤時間が不適切な値です。');
                        return; // 出退勤自体がおかしい場合は以降の休憩チェックをスキップ
                    }

                    // 休憩データの検証
                    $rests = $this->input('rests', []);
                    if (is_array($rests)) {
                        foreach ($rests as $key => $rest) {
                            $startStr = $rest['start_time'] ?? null;
                            $endStr   = $rest['end_time'] ?? null;

                            // 片方だけ入力されているケースはルール側（required_with）で弾くので、両方ある場合のみ論理検証
                            if ($startStr && $endStr) {
                                $start = Carbon::createFromFormat('H:i', $startStr);
                                $end   = Carbon::createFromFormat('H:i', $endStr);

                                // ② 休憩終了が休憩開始より前または同じ場合
                                if (!$end->greaterThan($start)) {
                                    $validator->errors()->add("rests.{$key}.end_time", '休憩時間が不適切な値です。');
                                }

                                // ③ 休憩開始が出勤より前、あるいは退勤より後の場合
                                if (!$start->greaterThan($checkIn) || !$start->lessThan($checkOut)) {
                                    $validator->errors()->add("rests.{$key}.start_time", '休憩時間が不適切な値です。');
                                }

                                // ④ 休憩終了が退勤以降の場合
                                if (!$end->lessThan($checkOut)) {
                                    $validator->errors()->add("rests.{$key}.end_time", '休憩時間もしくは退勤時間が不適切な値です。');
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // 時刻パースエラー時は基本ルール側が検知するので何もしない
                }
            }
        });
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
