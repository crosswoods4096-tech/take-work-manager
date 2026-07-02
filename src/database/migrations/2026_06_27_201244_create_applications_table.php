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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('target_date')->comment('修正対象の日付');
            $table->string('type')->default('fix')->comment('申請タイプ: 休暇、打刻修正など');
            $table->string('status')->default('pending')->comment('承認ステータス: pending, approved, rejected');
            // 👈 修正したい時間を一時保存するカラムを追加
            $table->time('requested_check_in')->nullable();
            $table->time('requested_check_out')->nullable();
            $table->time('requested_rest_start_1')->nullable();
            $table->time('requested_rest_end_1')->nullable();
            $table->time('requested_rest_start_2')->nullable();
            $table->time('requested_rest_end_2')->nullable();

            $table->text('reason')->nullable()->comment('申請理由');
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
