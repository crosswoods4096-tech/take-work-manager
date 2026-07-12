<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    /**
     * ② 日次勤怠一覧画面の表示
     */
    public function dailyList(Request $request)
    {
        // 1. 表示したい日付を取得（指定がなければ今日の日付）
        $dateString = $request->query('date', Carbon::today()->format('Y-m-d'));
        $currentDate = Carbon::parse($dateString);

        // 前日・翌日の日付をビューに渡すために用意
        $prevDate = $currentDate->copy()->subDay()->format('Y-m-d');
        $nextDate = $currentDate->copy()->addDay()->format('Y-m-d');

        // 2. 全「一般ユーザー（general）」をベースに、その日の勤怠と休憩データをまとめて取得
        // 💡 勤怠がないスタッフも「未出勤」として一覧に出すため、User側からリレーションを引きます
        $users = User::where('role', 'general')
            ->with(['attendances' => function ($query) use ($dateString) {
                $query->where('date', $dateString)->with('rests');
            }])
            ->get();

        // 3. ビューにデータを渡して表示
        return view('admin.attendance.daily', compact('users', 'currentDate', 'prevDate', 'nextDate'));
    }
    /**
     * ③ 勤怠詳細画面の表示（管理者用）
     */
    public function showDetail($id)
    {
        // 勤怠データと、紐づくユーザー・すべての休憩データを一撃で取得
        $attendance = Attendance::with(['user', 'rests'])->findOrFail($id);

        return view('admin.attendance.detail', compact('attendance'));
    }

    /**
     * ③ 勤怠データの直接修正保存（管理者用・分単位の自動再計算付き）
     */
    public function updateAttendance(\Illuminate\Http\Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        // 1. バリデーション
        $request->validate([
            'check_in'  => ['required', 'date_format:H:i'],
            'check_out' => ['required', 'date_format:H:i'],
        ]);

        // 2. 出退勤時間の更新（まずは時間を文字列として保存）
        $attendance->update([
            'check_in'  => $request->check_in . ':00',
            'check_out' => $request->check_out . ':00',
        ]);

        // 3. 休憩時間の更新・新規追加（新枠 'new' も対応）
        if ($request->has('rests')) {
            foreach ($request->rests as $restId => $restTimes) {
                if (!empty($restTimes['start_time']) && !empty($restTimes['end_time'])) {
                    if ($restId === 'new') {
                        $attendance->rests()->create([
                            'start_time' => $restTimes['start_time'] . ':00',
                            'end_time'   => $restTimes['end_time'] . ':00',
                        ]);
                    } else {
                        $rest = $attendance->rests()->find($restId);
                        if ($rest) {
                            $rest->update([
                                'start_time' => $restTimes['start_time'] . ':00',
                                'end_time'   => $restTimes['end_time'] . ':00',
                            ]);
                        }
                    }
                }
            }
        }

        // 💡 4. 【重要】管理者が変更した時間を元に、総拘束時間と総休憩時間を「分」で再計算する
        $attendance->refresh(); // データベースの最新の休憩データを再読み込み

        // 拘束時間の計算 (出勤 〜 退勤)
        $checkIn  = \Carbon\Carbon::parse($attendance->check_in);
        $checkOut = \Carbon\Carbon::parse($attendance->check_out);
        $totalWorkingMinutes = $checkIn->diffInMinutes($checkOut);

        // 休憩時間の合計計算（すべての休憩をループして合計分を出す）
        $totalBreakMinutes = 0;
        foreach ($attendance->rests as $rest) {
            if ($rest->start_time && $rest->end_time) {
                $start = \Carbon\Carbon::parse($rest->start_time);
                $end   = \Carbon\Carbon::parse($rest->end_time);
                $totalBreakMinutes += $start->diffInMinutes($end);
            }
        }

        // 計算した数値をデータベースに上書き保存！
        $attendance->update([
            'total_working_hours' => $totalWorkingMinutes,
            'total_break_time'    => $totalBreakMinutes,
        ]);

        // 5. 日次勤怠一覧画面へ戻る
        $formattedDate = \Carbon\Carbon::parse($attendance->date)->format('Y-m-d');
        return redirect()->route('admin.attendance.daily', ['date' => $formattedDate])
            ->with('success', "{$attendance->user->name}さんの勤怠データを修正しました。");
    }
}
