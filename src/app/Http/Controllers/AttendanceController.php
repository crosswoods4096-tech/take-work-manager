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
}
