<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    // 👇 これを追加して、まとめて保存（一括代入）を許可します
    protected $fillable = [
        'user_id',
        'date',
        'check_in',
        'check_out',
        'total_working_hours',
        'total_break_time',
    ];

    /**
     * リレーション定義：この勤怠データはどのユーザーのものか
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function rests()
    {
        return $this->hasMany(Rest::class);
    }
}
