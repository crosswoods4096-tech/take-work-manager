<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 💡 すでに同じメールアドレスの管理者がいないかチェックし、なければ作成する
        User::updateOrCreate(
            ['email' => 'admin@example.com'], // 検索条件（重複登録の防止）
            [
                'name'     => 'システム管理者',
                'password' => Hash::make('password123'), // 💡 パスワードは必ずハッシュ化（暗号化）して保存
                'role'     => 'admin', // 💡 ここで管理者として設定！
            ]
        );
    }
}
