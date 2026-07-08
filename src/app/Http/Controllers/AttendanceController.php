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
        $user = Auth::user();
        $today = Carbon::today()->format('Y-m-d');
        $status = 'outside';

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($attendance) {
            if (!empty($attendance->check_out)) {
                $status = 'left';
            } else {
                $latestRest = Rest::where('attendance_id', $attendance->id)
                    ->latest()
                    ->first();

                if ($latestRest && empty($latestRest->end_time)) {
                    $status = 'resting';
                } else {
                    $status = 'working';
                }
            }
        }

        return view('attendance.register', compact('status'));
    }

    // ① 出勤ボタンが押されたときの処理
    public function checkIn(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();
        $today = $now->format('Y-m-d');

        $exists = Attendance::where('user_id', $user->id)->where('date', $today)->exists();
        if ($exists) {
            return redirect()->back()->with('error', 'すでに出勤打刻済みです。');
        }

        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'check_in' => $now->format('H:i:s'),
        ]);

        return redirect()->back()->with('success', '出勤しました。');
    }

    // ② 退勤ボタンが押されたときの処理（💡 分単位の整数に全面修正）
    public function checkOut(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();
        $today = $now->format('Y-m-d');
        $checkOutTime = $now->format('H:i:s');

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->whereNull('check_out')
            ->first();

        if (!$attendance) {
            return redirect()->back()->with('error', '出勤データが見つかりません。');
        }

        // ------------------------------------------------------------
        // 2. 総休憩時間の計算（💡 分単位の整数に修正）
        // ------------------------------------------------------------
        $totalBreakMinutes = 0;

        $rests = Rest::where('attendance_id', $attendance->id)
            ->whereNotNull('start_time')
            ->whereNotNull('end_time')
            ->get();

        foreach ($rests as $rest) {
            $start = Carbon::parse($rest->start_time);
            $end = Carbon::parse($rest->end_time);
            // 💡 差分を「分」で直接足していく
            $totalBreakMinutes += $start->diffInMinutes($end);
        }

        // ------------------------------------------------------------
        // 3. 総勤務時間の計算（💡 出勤〜退勤の純粋な拘束時間を分で計算）
        // ------------------------------------------------------------
        $checkIn = Carbon::parse($attendance->check_in);
        $checkOut = Carbon::parse($checkOutTime);
        $totalWorkingMinutes = $checkIn->diffInMinutes($checkOut);

        // ------------------------------------------------------------
        // 4. データベースの更新（💡 整数をそのまま保存！）
        // ------------------------------------------------------------
        $attendance->update([
            'check_out' => $checkOutTime,
            'total_working_hours' => $totalWorkingMinutes, // 整数
            'total_break_time' => $totalBreakMinutes,     // 整数
        ]);

        return redirect()->back()->with('success', '退勤しました。お疲れ様でした！');
    }

    // ③ 休憩入ボタンが押されたときの処理
    public function startRest(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();
        $today = $now->format('Y-m-d');

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->whereNull('check_out')
            ->first();

        if (!$attendance) {
            return redirect()->back()->with('error', '出勤データが見つかりません。');
        }

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

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->whereNull('check_out')
            ->first();

        if (!$attendance) {
            return redirect()->back()->with('error', '出勤データが見つかりません。');
        }

        $rest = Rest::where('attendance_id', $attendance->id)
            ->whereNull('end_time')
            ->latest()
            ->first();

        if (!$rest) {
            return redirect()->back()->with('error', '休憩開始データが見つかりません。');
        }

        $rest->update([
            'end_time' => $now->format('H:i:s'),
        ]);

        return redirect()->back()->with('success', '休憩から戻りました。');
    }

    // 💡 勤怠一覧画面の処理（データが整数になったので、引き算が超シンプルに！）
    public function list(Request $request)
    {
        $user = Auth::user();

        $monthParam = $request->query('month');
        if ($monthParam) {
            $currentMonth = Carbon::parse($monthParam)->startOfMonth();
        } else {
            $currentMonth = Carbon::today()->startOfMonth();
        }

        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $daysInMonth = [];
        $daysCount = $currentMonth->daysInMonth;
        for ($i = 0; $i < $daysCount; $i++) {
            $daysInMonth[] = $currentMonth->copy()->addDays($i);
        }

        $startOfMonth = $currentMonth->copy()->startOfMonth()->format('Y-m-d');
        $endOfMonth = $currentMonth->copy()->endOfMonth()->format('Y-m-d');

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy('date');

        // 💡 整数同士の単純な引き算に修正！
        foreach ($attendances as $record) {
            if ($record->total_working_hours !== null) {
                // 💡 すでに分（整数）なので、そのまま引き算するだけ！
                $workingMin = $record->total_working_hours;
                $breakMin = $record->total_break_time ?? 0;

                // 実働時間（分）
                $actualMin = $workingMin - $breakMin;

                if ($actualMin > 0) {
                    $hours = floor($actualMin / 60);
                    $minutes = $actualMin % 60;
                    // ビューで見やすいように "08:15" の形式にしてカスタム属性に持たせる
                    $record->actual_working_hours = sprintf('%02d:%02d', $hours, $minutes);
                } else {
                    $record->actual_working_hours = '00:00';
                }
            }
        }

        return view('attendance.index', compact(
            'currentMonth',
            'prevMonth',
            'nextMonth',
            'daysInMonth',
            'attendances'
        ));
    }

    public function detail($date)
    {
        $user = Auth::user();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $date)
            ->first();

        $rests = collect();
        if ($attendance) {
            $rests = Rest::where('attendance_id', $attendance->id)
                ->orderBy('start_time', 'asc')
                ->get();
        }

        return view('attendance.detail', compact('user', 'date', 'attendance', 'rests'));
    }

    public function report()
    {
        return view('reports.index');
    }
}
