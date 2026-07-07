<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationRestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('application_rests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade'); // applications.id と紐付け
            $table->foreignId('rest_id')->constrained()->onDelete('cascade'); // どの休憩を直すか（rests.id）
            $table->Time('requested_start_time'); // 変更希望の休憩開始
            $table->Time('requested_end_time'); // 変更希望の休憩終了
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
        Schema::dropIfExists('application_rests');
    }
}
