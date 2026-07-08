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
        'attendance_id',
        'application_date',
        'status',
        'requested_check_in',
        'requested_check_out',
        'reason',
    ];

    /**
     * リレーション定義：この申請はどのユーザーのものか（多対1）
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // 💡 追記：修正申請（1）に対して、申請用休憩データ（多）を紐付けるリレーション
    public function applicationRests()
    {
        // ApplicationRest モデルと 1対多 (hasMany) の関係を結びます
        return $this->hasMany(ApplicationRest::class);
    }
}
