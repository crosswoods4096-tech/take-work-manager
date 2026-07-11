<?php

namespace App\Http\Controllers;

use App\Http\Requests\CorrectionRequest;
use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends Controller
{
    // 💡 2. 引数の「Request」を「CorrectionRequest」に書き換えます
    public function storeCorrection(CorrectionRequest $request, $date)
    {
        $userId = Auth::id();

        // ❌ コントローラ内の $request->validate([...]) の塊は丸ごと削除します
        // （ここに到達した時点で、すでにステップ1のバリデーションを通過しています）

        // 2. 二重申請の防止
        $alreadyApplied = Application::where('user_id', $userId)
            ->where('application_date', $date)
            ->where('status', 'pending')
            ->exists();

        if ($alreadyApplied) {
            return redirect()->back()->with('error', 'この日付に対する修正申請は既に提出され、承認待ちです。');
        }

        // 元になる当日の勤怠レコードを取得
        $attendance = Attendance::where('user_id', $userId)
            ->where('date', $date)
            ->first();

        if (!$attendance) {
            return redirect()->back()->with('error', '元となる勤怠データが見つかりません。');
        }

        // 3. applications テーブルに保存
        $application = Application::create([
            'user_id'              => $userId,
            'attendance_id'        => $attendance->id,
            'application_date'     => $date,
            'type'                 => 'fix',
            'status'               => 'pending',
            'requested_check_in'   => $request->check_in . ':00',
            'requested_check_out'  => $request->check_out . ':00',
            'reason'               => $request->remarks,
        ]);

        // 4. 休憩の修正申請データを「何個でも（新枠も含む）」ループで保存
        if ($request->has('rests')) {
            foreach ($request->rests as $restId => $restTimes) {
                if (!empty($restTimes['start_time']) && !empty($restTimes['end_time'])) {

                    // 💡 もし新規追加枠（キーが 'new'）なら、rest_id は null にする
                    $actualRestId = ($restId === 'new') ? null : $restId;

                    $application->applicationRests()->create([
                        'rest_id'              => $actualRestId, // 💡 ここに判定したIDを入れる
                        'requested_start_time' => $restTimes['start_time'] . ':00',
                        'requested_end_time'   => $restTimes['end_time'] . ':00',
                    ]);
                }
            }
        }

        return redirect()->route('attendance.index')->with('success', '修正申請を提出しました。');
    }
    // 申請一覧画面の処理
    public function index(Request $request)
    {
        // 1. 現在表示すべきタブを取得（デフォルトは 'pending': 承認待ち）
        $activeTab = $request->query('tab', 'pending');

        // 2. ログイン中のユーザーの申請データを取得
        // Eager Loading（with('user')）を使って、名前の表示を高速化します
        $query = Application::where('user_id', Auth::id())->with('user');

        if ($activeTab === 'approved') {
            // 「承認済み」タブの場合は、approved（承認）または rejected（却下）のデータを取得
            $applications = $query->whereIn('status', ['approved', 'rejected'])
                ->orderBy('updated_at', 'desc')
                ->get();
        } else {
            // デフォルト（承認待ち）タブの場合は、pending のデータを取得
            $applications = $query->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // 3. ご報告いただいた「applicate」フォルダのビューに変数を入れて返す
        return view('applicate.index', compact('applications', 'activeTab'));
    }
    // 💡 今後、ここに「申請のキャンセル（destroy）」などを追記していきます！
}
