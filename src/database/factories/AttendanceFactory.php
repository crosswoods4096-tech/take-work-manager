<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'date' => $this->faker->date(),
            'check_in' => '09:00:00',
            'check_out' => '18:00:00',
            'total_working_hours' => 540,
            'total_break_time' => 60,
        ];
    }
}
