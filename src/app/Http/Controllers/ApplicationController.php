<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends Controller
{
    // 勤怠詳細画面から「修正申請」がPOSTされたときの処理
    public function storeCorrection(Request $request, $date)
    {
        // 1. バリデーション
        $request->validate([
            'remarks' => ['required', 'string', 'max:1000'],
        ], [
            'remarks.required' => '修正を申請する場合は、備考欄に申請理由を入力してください。',
        ]);

        // 2. 二重申請の防止
        $alreadyApplied = Application::where('user_id', $user = Auth::id()) // Auth::id()でIDのみスマートに取得
            ->where('target_date', $date)
            ->where('status', 'pending')
            ->exists();

        if ($alreadyApplied) {
            return redirect()->back()->with('error', 'この日付に対する修正申請は既に提出され、承認待ちです。');
        }

        // 3. applications テーブルに保存
        Application::create([
            'user_id'              => Auth::id(),
            'target_date'          => $date,
            'type'                 => 'fix',
            'status'               => 'pending',
            'requested_check_in'   => $request->check_in,
            'requested_check_out'  => $request->check_out,
            'requested_rest_start_1' => $request->rest_start_1,
            'requested_rest_end_1'   => $request->rest_end_1,
            'requested_rest_start_2' => $request->rest_start_2,
            'requested_rest_end_2'   => $request->rest_end_2,
            'reason'               => $request->remarks,
        ]);

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
