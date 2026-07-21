<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Application;
use App\Models\Rest;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,        // ① まず一般ユーザー5人＆管理者を生成
            AttendanceSeeder::class,  // ② 次にそのユーザーたちの1月〜6月分の勤怠データを生成
        ]);
    }
}
