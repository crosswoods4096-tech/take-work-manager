<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationRest extends Model
{
    use HasFactory;

    // 💡 複数代入（保存）を許可するカラムを指定
    protected $fillable = [
        'application_id',
        'rest_id',
        'requested_start_time',
        'requested_end_time',
    ];

    // 💡 修正申請（親）へのリレーション
    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
