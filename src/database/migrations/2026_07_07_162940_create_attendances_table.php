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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->Time('check_in')->nullable();
            $table->Time('check_out')->nullable();


            // 整数（integer）型で、初期値は0、NULLも許可しておきます
            $table->integer('total_working_hours')->default(0)->nullable()->comment('拘束時間（分単位）');
            $table->integer('total_break_time')->default(0)->nullable()->comment('総休憩時間（分単位）');

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
