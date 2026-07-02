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
            'user_id' => User::factory(),
            'target_date' => $this->faker->date(),
            'type' => 'fix',
            'status' => 'pending',
            'reason' => '打刻漏れのため修正を申請します。',
        ];
    }
}
