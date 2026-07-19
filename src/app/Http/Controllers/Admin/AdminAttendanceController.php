<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CorrectionRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Application;
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
    public function updateAttendance(\App\Http\Requests\CorrectionRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        // ⭕️ 修正：手動の $request->validate(...) は削除します。
        // （メソッドに入った時点で CorrectionRequest のオリジナルバリデーションが自動完了しています）

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

        // 4. 【重要】管理者が変更した時間を元に、総拘束時間と総休憩時間を「分」で再計算する
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
    /**
     * ④ スタッフ一覧画面の表示
     */
    public function staffList()
    {
        // 管理者以外の「一般スタッフ」を全員取得する
        $staffs = User::where('role', 'general')
            ->orderBy('id', 'asc')
            ->get();

        return view('admin.staff.index', compact('staffs'));
    }
    /**
     * ⑤ スタッフごとの月次勤怠一覧表示
     */
    public function staffMonthlyAttendance(Request $request, $id)
    {
        // 1. 対象のスタッフ情報を取得
        $staff = User::where('role', 'general')->findOrFail($id);

        // 2. 表示したい「月」を取得（デフォルトは当月）
        $monthParam = $request->query('month');
        if ($monthParam) {
            $currentMonth = Carbon::parse($monthParam)->startOfMonth();
        } else {
            $currentMonth = Carbon::today()->startOfMonth();
        }

        // 前月・次月のパラメータ用文字列
        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        // 3. 1ヶ月分の日付配列を生成
        $daysInMonth = [];
        $daysCount = $currentMonth->daysInMonth;
        for ($i = 0; $i < $daysCount; $i++) {
            $daysInMonth[] = $currentMonth->copy()->addDays($i);
        }

        $startOfMonth = $currentMonth->copy()->startOfMonth()->format('Y-m-d');
        $endOfMonth = $currentMonth->copy()->endOfMonth()->format('Y-m-d');

        // 4. そのスタッフの該当月の勤怠データをまとめて取得
        $attendances = Attendance::where('user_id', $staff->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy('date');

        // 5. 実働時間の計算（分単位 ➔ H:i 形式に変換）
        foreach ($attendances as $record) {
            if ($record->total_working_hours !== null) {
                $workingMin = $record->total_working_hours;
                $breakMin = $record->total_break_time ?? 0;
                $actualMin = $workingMin - $breakMin;

                if ($actualMin > 0) {
                    $hours = floor($actualMin / 60);
                    $minutes = $actualMin % 60;
                    $record->actual_working_hours = sprintf('%02d:%02d', $hours, $minutes);
                } else {
                    $record->actual_working_hours = '00:00';
                }
            }
        }

        return view('admin.staff.attendance', compact(
            'staff',
            'currentMonth',
            'prevMonth',
            'nextMonth',
            'daysInMonth',
            'attendances'
        ));
    }
    /**
     * ⑥ 修正申請一覧画面の表示
     */
    public function applicationList(Request $request)
    {
        // タブの切り替え（デフォルトは承認待ち 'pending'）
        $activeTab = $request->query('tab', 'pending');

        $query = Application::with(['user']);

        if ($activeTab === 'approved') {
            $applications = $query->whereIn('status', ['approved', 'rejected'])
                ->orderBy('updated_at', 'desc')->get();
        } else {
            $applications = $query->where('status', 'pending')
                ->orderBy('created_at', 'desc')->get();
        }

        return view('admin.applicate.index', compact('applications', 'activeTab'));
    }

    /**
     * ⑦ 修正申請詳細画面の表示
     */
    public function showApplication($id)
    {
        // 申請データと、元の勤怠データ、申請された休憩データを取得
        // 💡 以前作成した applicationRests リレーションを利用します
        $application = Application::with(['user', 'attendance', 'applicationRests'])->findOrFail($id);

        return view('admin.applicate.show', compact('application'));
    }

    /**
     * ⑦ 修正申請の「承認」処理
     */
    public function approveApplication($id)
    {
        $application = Application::with('applicationRests')->findOrFail($id);
        $attendance = Attendance::findOrFail($application->attendance_id);

        // 1. 申請された出退勤時間を、本番の勤怠レコードに上書き反映
        $attendance->update([
            'check_in'  => $application->requested_check_in,
            'check_out' => $application->requested_check_out,
        ]);

        // 2. 既存の本番休憩データを一旦削除し、申請された休憩データに丸ごと差し替える
        $attendance->rests()->delete();
        foreach ($application->applicationRests as $appRest) {
            $attendance->rests()->create([
                'start_time' => $appRest->start_time,
                'end_time'   => $appRest->end_time,
            ]);
        }

        // 3. 【リアルタイム再計算】本番の勤怠データを基に総実働・総休憩を「分」で計算して更新
        $attendance->refresh();
        $checkIn  = \Carbon\Carbon::parse($attendance->check_in);
        $checkOut = \Carbon\Carbon::parse($attendance->check_out);
        $totalWorkingMinutes = $checkIn->diffInMinutes($checkOut);

        $totalBreakMinutes = 0;
        foreach ($attendance->rests as $rest) {
            if ($rest->start_time && $rest->end_time) {
                $totalBreakMinutes += \Carbon\Carbon::parse($rest->start_time)->diffInMinutes(\Carbon\Carbon::parse($rest->end_time));
            }
        }

        $attendance->update([
            'total_working_hours' => $totalWorkingMinutes,
            'total_break_time'    => $totalBreakMinutes,
        ]);

        // 4. 申請ステータスを「承認済み」に変更
        $application->update(['status' => 'approved']);

        return redirect()->route('admin.application.index')->with('success', '申請を承認し、勤怠データに反映しました。');
    }

    /**
     * ⑦ 修正申請の「却下」処理
     */
    public function rejectApplication($id)
    {
        $application = Application::findOrFail($id);

        // ステータスを「却下」に変更（本番の勤怠データは弄らない）
        $application->update(['status' => 'rejected']);

        return redirect()->route('admin.application.index')->with('success', '申請を却下しました。');
    }
}
