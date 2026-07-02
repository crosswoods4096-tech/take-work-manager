<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rest extends Model
{
    use HasFactory;

    // 👇 休憩レコード用の許可設定
    protected $fillable = [
        'attendance_id',
        'start_time',
        'end_time',
    ];
}
