<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();

        // ---------------------------------------------------------
        // 1. 過去6ヶ月（当月含む）の期間設定
        // ---------------------------------------------------------
        $sixMonthsAgo = $today->copy()->subMonths(5)->startOfMonth();
        $endOfThisMonth = $today->copy()->endOfMonth();

        // 過去6ヶ月間の該当ユーザーの勤怠データを一括取得
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$sixMonthsAgo->format('Y-m-d'), $endOfThisMonth->format('Y-m-d')])
            ->get();

        // ---------------------------------------------------------
        // 2. 「基本サマリー」の集計（過去6ヶ月全体）
        // ---------------------------------------------------------
        $totalWorkingMinutes = 0; // 総労働時間（分）
        $totalOvertimeMinutes = 0; // 総残業時間（分）
        $workingDaysCount = 0; // 実働日数（平均算出用）

        // 所定労働時間（1日8時間 = 480分）を超える分を残業時間とする
        $regularWorkingMinutesPerDay = 480;

        foreach ($attendances as $record) {
            if ($record->total_working_hours !== null) {
                // 実労働時間 = 総労働時間 - 総休憩時間
                $breakMin = $record->total_break_time ?? 0;
                $actualMin = $record->total_working_hours - $breakMin;

                if ($actualMin > 0) {
                    $totalWorkingMinutes += $actualMin;
                    $workingDaysCount++;

                    // 残業時間の計算（8時間を超えた分）
                    if ($actualMin > $regularWorkingMinutesPerDay) {
                        $totalOvertimeMinutes += ($actualMin - $regularWorkingMinutesPerDay);
                    }
                }
            }
        }

        // 平均労働時間の計算（1日あたり）
        $avgWorkingMinutes = $workingDaysCount > 0 ? round($totalWorkingMinutes / $workingDaysCount) : 0;

        // サマリー表示用に「時間と分」にフォーマット
        $summary = [
            'total_work' => $this->formatMinutesToHoursAndMinutes($totalWorkingMinutes),
            'total_over' => $this->formatMinutesToHoursAndMinutes($totalOvertimeMinutes),
            'avg_work'   => $this->formatMinutesToHoursAndMinutes($avgWorkingMinutes),
        ];

        // ---------------------------------------------------------
        // 3. 「月次推移(過去６ヶ月)」の集計
        // ---------------------------------------------------------
        $monthlyTrends = [];
        for ($i = 5; $i >= 0; $i--) {
            $targetMonth = $today->copy()->subMonths($i);
            $yearMonthStr = $targetMonth->format('Y-m'); // 検索キー

            // その月のデータのみをフィルタリング
            $monthlyRecords = $attendances->filter(function ($record) use ($yearMonthStr) {
                return Carbon::parse($record->date)->format('Y-m') === $yearMonthStr;
            });

            $mWorkMin = 0;
            $mOverMin = 0;

            foreach ($monthlyRecords as $record) {
                if ($record->total_working_hours !== null) {
                    $breakMin = $record->total_break_time ?? 0;
                    $actualMin = $record->total_working_hours - $breakMin;

                    if ($actualMin > 0) {
                        $mWorkMin += $actualMin;
                        if ($actualMin > $regularWorkingMinutesPerDay) {
                            $mOverMin += ($actualMin - $regularWorkingMinutesPerDay);
                        }
                    }
                }
            }

            $monthlyTrends[] = [
                'month'     => $targetMonth->format('Y年m月'),
                'work_time' => $this->formatMinutesToHoursAndMinutes($mWorkMin),
                'over_time' => $this->formatMinutesToHoursAndMinutes($mOverMin),
            ];
        }

        // ---------------------------------------------------------
        // 4. 「今月の異常検知」の集計
        // ---------------------------------------------------------
        // 基準：始業 09:00 / 終業 18:00 / 長時間労働は1日10時間（600分）超
        $startOfMonth = $today->copy()->startOfMonth()->format('Y-m-d');
        $endOfMonth = $today->copy()->endOfMonth()->format('Y-m-d');

        $thisMonthAttendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        $lateCount = 0;      // 遅刻回数
        $earlyLeaveCount = 0; // 早退回数
        $longWorkDays = 0;    // 長時間労働日数（10時間超 = 600分超）

        foreach ($thisMonthAttendances as $record) {
            // 遅刻検知（出勤が09:00より遅い）
            if ($record->check_in) {
                $checkInTime = Carbon::parse($record->check_in)->format('H:i');
                if ($checkInTime > '09:00') {
                    $lateCount++;
                }
            }

            // 早退検知（退勤が18:00より早い）
            if ($record->check_out) {
                $checkOutTime = Carbon::parse($record->check_out)->format('H:i');
                if ($checkOutTime < '18:00') {
                    $earlyLeaveCount++;
                }
            }

            // 長時間労働検知（実働が10時間 = 600分を超える）
            if ($record->total_working_hours !== null) {
                $breakMin = $record->total_break_time ?? 0;
                $actualMin = $record->total_working_hours - $breakMin;
                if ($actualMin > 600) {
                    $longWorkDays++;
                }
            }
        }

        $abnormalities = [
            'late'      => $lateCount,
            'early'     => $earlyLeaveCount,
            'long_work' => $longWorkDays,
        ];

        return view('reports.index', compact('summary', 'monthlyTrends', 'abnormalities'));
    }

    /**
     * 💡 ヘルパーメソッド：分単位の数値を「〇時間〇分」形式の連想配列に変換する
     */
    private function formatMinutesToHoursAndMinutes($totalMinutes)
    {
        if ($totalMinutes <= 0) {
            return ['hours' => 0, 'minutes' => 0];
        }
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        return [
            'hours'   => $hours,
            'minutes' => $minutes
        ];
    }
}
