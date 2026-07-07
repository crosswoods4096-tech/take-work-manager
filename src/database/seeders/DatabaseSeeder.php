<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\Application;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1. テストユーザーを3人作成
        $users = [
            User::create(['name' => '山田 太郎', 'email' => 'yamada@example.com', 'password' => Hash::make('password')]),
            User::create(['name' => '佐藤 花子', 'email' => 'sato@example.com', 'password' => Hash::make('password')]),
            User::create(['name' => '鈴木 一郎', 'email' => 'suzuki@example.com', 'password' => Hash::make('password')]),
        ];

        // 2026年6月の期間を設定
        $startDate = Carbon::create(2026, 6, 1);
        $endDate = Carbon::create(2026, 6, 30);

        foreach ($users as $user) {
            // 1日から30日まで1日ずつループ
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {

                // 土日はお休み（データを作らない＝欠勤・公休扱い）
                if ($date->isWeekend()) {
                    continue;
                }

                // 平日でも 10% の確率で「欠勤」とする（データを作らない）
                if (rand(1, 100) <= 10) {
                    continue;
                }

                // 基本の時間
                $checkIn = Carbon::parse('09:00:00');
                $checkOut = Carbon::parse('18:00:00');

                // 15% の確率で「遅刻」
                if (rand(1, 100) <= 15) {
                    $checkIn = Carbon::parse('10:15:00'); // 10時15分に出社
                }

                // 15% の確率で「早退」
                if (rand(1, 100) <= 15) {
                    $checkOut = Carbon::parse('16:45:00'); // 16時45分に退社
                }

                // 拘束（勤務）時間の計算

                $totalWorkingMinutes = $checkIn->diffInMinutes($checkOut);

                // 勤怠レコードの作成
                $attendance = Attendance::factory()->create([
                    'user_id' => $user->id,
                    'date' => $date->format('Y-m-d'),
                    'check_in' => $checkIn->format('H:i:s'),
                    'check_out' => $checkOut->format('H:i:s'),
                    'total_working_hours' => $totalWorkingMinutes,
                    'total_break_time' => 60, // 一旦1時間固定
                ]);

                // 休憩レコードの紐付け（12:00 ~ 13:00）
                Rest::factory()->create([
                    'attendance_id' => $attendance->id,
                    'start_time' => '12:00:00',
                    'end_time' => '13:00:00',
                ]);

                // 10% の確率で、この日に対して「修正申請（承認待ち）」が出されている状態にする
                if (rand(1, 100) <= 10) {
                    Application::factory()->create([
                        'user_id' => $user->id,
                        'attendance_id' => $attendance->id, // 👈 追加：どの勤務に対する申請かを紐付ける
                        'application_date' => $date->format('Y-m-d'), // 
                        'status' => 'pending',
                        'requested_check_in' => '09:00:00',
                        'requested_check_out' => '18:00:00',
                        'reason' => '打刻を忘れてしまったため、通常の勤務時間への修正をお願いします。',

                    ]);
                }

                // 5% の確率で「すでに承認済みの申請」も作っておく（タブ切り替え確認用）
                if (rand(1, 100) <= 5) {
                    Application::factory()->create([
                        'user_id' => $user->id,
                        'attendance_id' => $attendance->id, // 👈 追加：どの勤務に対する申請かを紐付ける
                        'application_date' => $date->format('Y-m-d'),
                        'status' => 'approved',
                        'requested_check_in' => '09:00:00',
                        'requested_check_out' => '18:00:00',
                        'reason' => '電車遅延による打刻修正です。',

                    ]);
                }
            }
        }
    }
}
