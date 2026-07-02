<?php

namespace Database\Factories;

use App\Models\Rest;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class RestFactory extends Factory
{
    protected $model = Rest::class;

    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(),
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ];
    }
}
