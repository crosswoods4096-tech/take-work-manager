<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ApplicationController;

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

    // 勤怠一覧画面
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.index');
    // 👇 これを追記：レポート画面用の張りぼてルート
    Route::get('/report', [AttendanceController::class, 'report'])->name('reports.index');
    // 申請一覧画面用のルート
    Route::get('/application/list', [ApplicationController::class, 'index'])->name('applicate.index');
    // 勤怠詳細画面（表示はAttendanceControllerが担当）
    Route::get('/attendance/detail/{date}', [AttendanceController::class, 'detail'])->name('attendance.detail');

    // 変更：ポスト先を ApplicationController に切り替え
    Route::post('/attendance/detail/{date}/update', [ApplicationController::class, 'storeCorrection'])->name('attendance.update');
});
