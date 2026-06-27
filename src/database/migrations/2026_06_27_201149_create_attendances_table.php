<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            // usersテーブルとの紐付け
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date')->comment('勤務日');
            $table->time('check_in')->comment('出勤時間');
            $table->time('check_out')->nullable()->comment('退勤時間');
            // 追加：総勤務時間と総休憩時間
            // ※退勤時や休憩終了時に計算して保存するため、初期値はnullableにするかデフォルト値を設定します
            $table->time('total_working_hours')->nullable()->comment('総勤務時間');
            $table->time('total_break_time')->nullable()->comment('総休憩時間');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}
