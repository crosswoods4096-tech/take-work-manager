<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    public function definition()
    {
        return [
            'user_id' => \App\Models\User::factory(), // 紐付けるユーザー
            'attendance_id' => null,                 // 一旦nullにしておく（シーダー側で上書きするため）


            'application_date' => $this->faker->date(), // ランダムな日付を自動生成

            'status' => 'pending',                    // デフォルトは承認待ち
            'reason' => $this->faker->realText(20),   // ランダムな日本語テキスト（20文字程度）
            'requested_check_in' => null,
            'requested_check_out' => null,


        ];
    }
}
