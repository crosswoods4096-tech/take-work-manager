@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/register.css') }}">
@endsection

@section('content')

{{-- 全体の背景色を薄いグレーにするため、コンテナで囲みます --}}
<div class="attendance-container">
    <div class="attendance-card">

        {{-- 1. 現在のステータス --}}
        <div class="status-badge">
            @if($status == 'outside')
            勤務外
            @elseif($status == 'working')
            出勤中
            @elseif($status == 'resting')
            休憩中
            @elseif($status == 'left')
            退勤済
            @endif
        </div>

        {{-- 2. 現在の日付と曜日 --}}
        <div class="current-date">
            {{ \Carbon\Carbon::now()->isoFormat('YYYY年MM月DD日(dddd)') }}
        </div>

        {{-- 3. 現時刻 --}}
        <div class="current-time" id="live-clock">
            {{ \Carbon\Carbon::now()->format('H:i:s') }}
        </div>

        {{-- 4. ステータスに応じたボタン・メッセージ --}}
        <div class="action-area">
            @if($status == 'outside')
            {{-- 勤務外：出勤ボタン（黒地に白） --}}
            <form action="{{ route('attendance.checkin') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-black">出勤</button>
            </form>

            @elseif($status == 'working')
            {{-- 出勤中：退勤（黒地に白）と 休憩入（白地に黒） --}}
            <div class="btn-group">
                <form action="{{ route('attendance.checkout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-black">退勤</button>
                </form>
                <form action="{{ route('attendance.rest.start') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-white">休憩入</button>
                </form>
            </div>

            @elseif($status == 'resting')
            {{-- 休憩中：休憩戻（白地に黒） --}}
            <form action="{{ route('attendance.rest.end') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-white">休憩戻</button>
            </form>

            @elseif($status == 'left')
            {{-- 退勤済：メッセージ --}}
            <p class="thanks-message">お疲れ様でした。</p>
            @endif
        </div>

    </div>
</div>

{{-- 時間をリアルタイムに動かすためのJavaScript --}}
<script>
    function updateClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('ja-JP', {
            hour12: false
        });
        document.getElementById('live-clock').textContent = timeString;
    }
    setInterval(updateClock, 1000);
</script>
@endsection