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
}
