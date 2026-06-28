<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;

// ==========================================
// ゲスト専用ルート（ログインしていない状態のみアクセス可能）
// ==========================================
Route::middleware(['guest'])->group(function () {
    // 会員登録
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // ログイン
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// ==========================================
// ログイン必須ルート（ログインしている状態のみアクセス可能）
// ==========================================
Route::middleware(['auth'])->group(function () {
    // ログアウト
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // 勤怠登録画面（前回作成したルート）
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.register');
    // 👇 ここから追記：打刻用POSTルート
    Route::post('/attendance/checkin', [AttendanceController::class, 'checkIn'])->name('attendance.checkin');
    Route::post('/attendance/checkout', [AttendanceController::class, 'checkOut'])->name('attendance.checkout');
    Route::post('/attendance/rest/start', [AttendanceController::class, 'startRest'])->name('attendance.rest.start');
    Route::post('/attendance/rest/end', [AttendanceController::class, 'endRest'])->name('attendance.rest.end');
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.index');

    // （仮）詳細画面用のルート（エラー回避のため名前だけ定義しておきます）
    Route::get('/attendance/detail/{date}', function ($date) {
        return "勤怠詳細ページ（日付: {$date}）※今後実装します";
    })->name('attendance.detail');
});
