<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    // 一括代入（Mass Assignment）を許可するカラムを定義
    protected $fillable = [
        'user_id',
        'target_date',
        'type',
        'status',
        'requested_check_in',
        'requested_check_out',
        'requested_rest_start_1',
        'requested_rest_end_1',
        'requested_rest_start_2',
        'requested_rest_end_2',
        'reason',
    ];

    /**
     * リレーション定義：この申請はどのユーザーのものか（多対1）
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
