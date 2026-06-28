<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;


// ログインしているユーザーのみアクセスできるように auth ミドルウェアを適用します
Route::middleware(['auth'])->group(function () {
    // 勤怠登録画面（打刻ページ）の表示
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.register');
});
