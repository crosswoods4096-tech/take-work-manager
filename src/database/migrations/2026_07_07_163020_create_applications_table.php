<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // 申請者
            $table->foreignId('attendance_id')->nullable()->constrained()->onDelete('cascade'); // 対象の勤務日（NULL可）
            $table->date('application_date'); // 申請対象日
            $table->string('status')->default('pending'); // 'pending' または 'approved'
            $table->text('reason'); // 申請・欠勤理由
            $table->Time('requested_check_in')->nullable(); // 変更希望の出勤時間
            $table->Time('requested_check_out')->nullable(); // 変更希望の退勤時間
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
        Schema::dropIfExists('applications');
    }
}
