<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminAttendanceController;

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
    // 申請一覧画面の表示 (GET)
    Route::get('/stamp_correction_request/list', [ApplicationController::class, 'index'])->name('applicate.index');
    // 勤怠詳細画面（表示はAttendanceControllerが担当）
    Route::get('/attendance/detail/{date}', [AttendanceController::class, 'detail'])->name('attendance.detail');

    // 💡 勤怠詳細からの修正申請（保存処理）

    Route::post('/attendance/{date}', [ApplicationController::class, 'storeCorrection'])->name('attendance.update');
});
// ==========================================
// 管理者専用ルート
// ==========================================
// 💡 1. 管理者認証（ログイン前でもアクセスできるルート）
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
});

// 💡 2. 管理者認証後（ログイン必須、かつ role が admin のユーザーのみ）
// ※ 簡易的に auth ミドルウェアをかけます。roleの制限はコントローラかミドルウェアで行います
Route::prefix('admin')->middleware(['auth'])->group(function () {
    // ログアウト
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    // ② 日次勤怠一覧画面（管理者のトップページ）
    Route::get('/attendance/daily', [AdminAttendanceController::class, 'dailyList'])->name('admin.attendance.daily');
});
