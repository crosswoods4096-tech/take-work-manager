<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Application;
use App\Models\Rest;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        // 1. 対象の一般ユーザーを取得
        $users = User::where('role', 'general')->get();
        if ($users->isEmpty()) {
            return;
        }

        // 本日の日付（上限用）
        $today = Carbon::today();

        // 💡 2. 期間を「今年の1月1日」〜「6月30日」に設定
        $startDate = Carbon::create(2026, 1, 1);
        $endDate   = Carbon::create(2026, 6, 30);

        foreach ($users as $user) {
            $currentDate = $startDate->copy();

            while ($currentDate->lte($endDate)) {
                // 土日はスキップ（平日のみ勤怠生成）
                if (!$currentDate->isWeekend()) {

                    // ① 勤怠レコードの作成
                    $attendance = Attendance::create([
                        'user_id'             => $user->id,
                        'date'                => $currentDate->format('Y-m-d'),
                        'check_in'            => '09:00:00',
                        'check_out'           => '18:00:00',
                        'total_working_hours' => 540, // 9時間 = 540分
                        'total_break_time'    => 60,  // 1時間 = 60分
                    ]);

                    // ② 休憩レコードの作成
                    Rest::create([
                        'attendance_id' => $attendance->id,
                        'start_time'    => '12:00:00',
                        'end_time'      => '13:00:00',
                    ]);

                    // ③ ランダムで修正申請データを作成（例：10%の確率で発生）
                    if (rand(1, 100) <= 10) {

                        // 💡 【重要】申請日（application_date）のロジック
                        // 対象日（$currentDate）から「本日」までの間でランダムな日付・時間を生成
                        $targetDate = $currentDate->copy();

                        if ($targetDate->lte($today)) {
                            // 対象日〜本日までの経過日数（0〜N日）
                            $diffInDays = $targetDate->diffInDays($today);
                            // ランダムな経過日数を足し、さらにランダムな時間（8:00〜20:00）をセット
                            $applicationDate = $targetDate->copy()
                                ->addDays(rand(0, $diffInDays))
                                ->setTime(rand(8, 20), rand(0, 59), rand(0, 59));

                            Application::create([
                                'user_id'              => $user->id,
                                'attendance_id'        => $attendance->id,
                                'status'               => 'pending', // 承認待ちなど
                                'requested_check_in'   => '08:30:00',
                                'requested_check_out'  => '18:30:00',
                                'reason'              => '打刻ミスのため修正申請します。',
                                'application_date'     => $applicationDate, // 💡 ランダム指定された日時
                            ]);
                        }
                    }
                }

                $currentDate->addDay();
            }
        }
    }
}
