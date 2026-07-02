<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        // 1. ログイン中のユーザーを取得
        $user = Auth::user();
        $today = Carbon::today()->format('Y-m-d');

        // デフォルトのステータスは「勤務外」
        $status = 'outside';

        // 2. 本日の出勤レコードがあるか確認
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($attendance) {
            if (!empty($attendance->check_out)) {
                // 退勤時間が記録されていれば「退勤済」
                $status = 'left';
            } else {
                // 退勤していない場合、最新の休憩レコードをチェック
                $latestRest = Rest::where('attendance_id', $attendance->id)
                    ->latest()
                    ->first();

                if ($latestRest && empty($latestRest->end_time)) {
                    // 休憩開始しているが、終了時間が空なら「休憩中」
                    $status = 'resting';
                } else {
                    // それ以外は「出勤中」
                    $status = 'working';
                }
            }
        }

        // 3. 判定したステータスをビューに渡す
        return view('attendance.register', compact('status'));
    }

    // ① 出勤ボタンが押されたときの処理
    public function checkIn(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();
        $today = $now->format('Y-m-d');

        // 二重打刻防止：すでに今日出勤データがあればエラー、またはリダイレクト
        $exists = Attendance::where('user_id', $user->id)->where('date', $today)->exists();
        if ($exists) {
            return redirect()->back()->with('error', 'すでに出勤打刻済みです。');
        }

        // 新しい出勤レコードを作成
        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'check_in' => $now->format('H:i:s'),
        ]);

        return redirect()->back()->with('success', '出勤しました。');
    }

    // ② 退勤ボタンが押されたときの処理
    public function checkOut(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();
        $today = $now->format('Y-m-d');
        $checkOutTime = $now->format('H:i:s');

        // 1. 本日の出勤レコード（退勤前）を取得
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->whereNull('check_out')
            ->first();

        if (!$attendance) {
            return redirect()->back()->with('error', '出勤データが見つかりません。');
        }

        // ------------------------------------------------------------
        // 2. 総休憩時間の計算
        // ------------------------------------------------------------
        $totalBreakSeconds = 0;

        // この勤務に紐づくすべての休憩レコード（開始と終了が揃っているもの）を取得
        $rests = Rest::where('attendance_id', $attendance->id)
            ->whereNotNull('start_time')
            ->whereNotNull('end_time')
            ->get();

        foreach ($rests as $rest) {
            $start = Carbon::parse($rest->start_time);
            $end = Carbon::parse($rest->end_time);
            // 休憩時間の差分（秒）を足していく
            $totalBreakSeconds += $start->diffInSeconds($end);
        }

        // 秒を「H:i:s」形式の文字列に変換
        $totalBreakTimeStr = null;
        if ($totalBreakSeconds > 0) {
            $breakHours = floor($totalBreakSeconds / 3600);
            $breakMinutes = floor(($totalBreakSeconds % 3600) / 60);
            $breakSecs = $totalBreakSeconds % 60;
            $totalBreakTimeStr = sprintf('%02d:%02d:%02d', $breakHours, $breakMinutes, $breakSecs);
        } else {
            $totalBreakTimeStr = '00:00:00';
        }

        // ------------------------------------------------------------
        // 3. 総勤務時間の計算（出勤〜退勤の純粋な拘束時間）
        // ------------------------------------------------------------
        $checkIn = Carbon::parse($attendance->check_in);
        $checkOut = Carbon::parse($checkOutTime);
        $totalWorkingSeconds = $checkIn->diffInSeconds($checkOut);

        $workingHours = floor($totalWorkingSeconds / 3600);
        $workingMinutes = floor(($totalWorkingSeconds % 3600) / 60);
        $workingSecs = $totalWorkingSeconds % 60;
        $totalWorkingHoursStr = sprintf('%02d:%02d:%02d', $workingHours, $workingMinutes, $workingSecs);

        // ------------------------------------------------------------
        // 4. データベースの更新
        // ------------------------------------------------------------
        $attendance->update([
            'check_out' => $checkOutTime,
            'total_working_hours' => $totalWorkingHoursStr,
            'total_break_time' => $totalBreakTimeStr,
        ]);

        return redirect()->back()->with('success', '退勤しました。お疲れ様でした！');
    }

    // ③ 休憩入ボタンが押されたときの処理
    public function startRest(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();
        $today = $now->format('Y-m-d');

        // 本日の出勤レコードを取得
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->whereNull('check_out')
            ->first();

        if (!$attendance) {
            return redirect()->back()->with('error', '出勤データが見つかりません。');
        }

        // 休憩開始レコードを作成
        Rest::create([
            'attendance_id' => $attendance->id,
            'start_time' => $now->format('H:i:s'),
        ]);

        return redirect()->back()->with('success', '休憩に入りました。');
    }

    // ④ 休憩戻ボタンが押されたときの処理
    public function endRest(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();
        $today = $now->format('Y-m-d');

        // 本日の出勤レコードを取得
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->whereNull('check_out')
            ->first();

        if (!$attendance) {
            return redirect()->back()->with('error', '出勤データが見つかりません。');
        }

        // 終了時間が空の最新の休憩レコードを取得
        $rest = Rest::where('attendance_id', $attendance->id)
            ->whereNull('end_time')
            ->latest()
            ->first();

        if (!$rest) {
            return redirect()->back()->with('error', '休憩開始データが見つかりません。');
        }

        // 休憩終了時間を記録
        $rest->update([
            'end_time' => $now->format('H:i:s'),
        ]);

        return redirect()->back()->with('success', '休憩から戻りました。');
    }
    public function list(Request $request)
    {
        $user = Auth::user();

        // 1. 表示対象の年月を取得（リクエストになければ今月）
        $monthParam = $request->query('month'); // '2026-06' などの形式を想定
        if ($monthParam) {
            $currentMonth = Carbon::parse($monthParam)->startOfMonth();
        } else {
            $currentMonth = Carbon::today()->startOfMonth();
        }

        // 2. 先月・次月の文字列を生成（移動リンク用）
        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        // 3. 対象月の日数を取得し、1日から月末までの全日付の配列を作成
        $daysInMonth = [];
        $daysCount = $currentMonth->daysInMonth;
        for ($i = 0; $i < $daysCount; $i++) {
            $daysInMonth[] = $currentMonth->copy()->addDays($i);
        }

        // 4. データベースから対象月の勤怠データを取得
        $startOfMonth = $currentMonth->copy()->startOfMonth()->format('Y-m-d');
        $endOfMonth = $currentMonth->copy()->endOfMonth()->format('Y-m-d');

        // 日付をキーにした連想配列（コレクション）にしておくと、Blade側で検索しやすくなります
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy('date');

        // 💡 合計時間（実働時間）の計算ロジック
        // 各レコードに対して「総勤務時間 - 総休憩時間」を計算してカスタム属性を持たせます
        foreach ($attendances as $record) {
            if ($record->total_working_hours) {
                $workingSec = Carbon::parse($record->total_working_hours)->secondsSinceMidnight();
                $breakSec = $record->total_break_time ? Carbon::parse($record->total_break_time)->secondsSinceMidnight() : 0;

                // 実働時間（秒）
                $actualSec = $workingSec - $breakSec;

                if ($actualSec > 0) {
                    $hours = floor($actualSec / 3600);
                    $minutes = floor(($actualSec % 3600) / 60);
                    // 例: "08:15" の形式で保存
                    $record->actual_working_hours = sprintf('%02d:%02d', $hours, $minutes);
                } else {
                    $record->actual_working_hours = '00:00';
                }
            }
        }

        // 5. ビューに変数を渡して表示
        return view('attendance.index', compact(
            'currentMonth',
            'prevMonth',
            'nextMonth',
            'daysInMonth',
            'attendances'
        ));
    }
    // --- クラス内に勤怠詳細メソッドを追記 ---

    public function detail($date)
    {
        $user = Auth::user();

        // 1. 指定された日付の出勤レコードを取得
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $date)
            ->first();

        // 2. 出勤レコードがある場合、紐づく休憩レコードを古い順に取得
        $rests = collect(); // データがない時のために空のコレクションを用意
        if ($attendance) {
            $rests = Rest::where('attendance_id', $attendance->id)
                ->orderBy('start_time', 'asc')
                ->get();
        }

        // 3. ビューにデータを渡す
        return view('attendance.detail', compact('user', 'date', 'attendance', 'rests'));
    }
    //動作確認用
    public function report()
    {
        // 張りぼてのビューを返すだけ
        return view('reports.index');
    }
}
