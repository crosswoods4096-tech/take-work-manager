<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. 管理者ユーザー（ログインテスト等で必要な場合用に1人作っておきます）
        User::create([
            'name'     => '管理者 太郎',
            'email'    => 'admin@example.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        // 2. 一般ユーザー 5名
        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'name'     => 'テスト ユーザー' . $i,
                'email'    => 'user' . $i . '@example.com',
                'password' => Hash::make('password'),
                'role'     => 'general',
            ]);
        }
    }
}
