@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/index.css') }}">
@endsection

@section('content')

{{-- 全体の背景を薄いグレーにするためコンテナで囲みます --}}
<div class="attendance-container">
    {{-- 1. タイトル（左側の黒ライン付き） --}}
    <h2 class="page-title">勤怠一覧</h2>

    {{-- 2. 月移動ナビゲーションバー --}}
    <div class="month-navigation">
        {{-- 前月ボタン --}}
        <a href="{{ route('attendance.index', ['month' => $prevMonth]) }}" class="nav-btn prev-btn">
            &larr; 前月
        </a>

        {{-- 現在表示中の年月 ＆ カレンダーアイコン --}}
        <div class="current-month">
            <span class="calendar-icon">📅</span>
            <span class="month-text">{{ $currentMonth->format('Y/m') }}</span>
        </div>

        {{-- 翌月ボタン --}}
        <a href="{{ route('attendance.index', ['month' => $nextMonth]) }}" class="nav-btn next-btn">
            翌月 &rarr;
        </a>
    </div>

    {{-- 3. 勤怠一覧テーブル（ひと月分をループ） --}}
    <div class="table-responsive">
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                {{-- コントローラーから渡された1ヶ月分の日付データをループします --}}
                @foreach($daysInMonth as $day)
                @php
                // その日の勤怠データがあるか探す
                $record = $attendances->get($day->format('Y-m-d'));
                @endphp
                <tr>
                    {{-- 日付（曜日含む） --}}
                    <td class="col-date">{{ $day->isoFormat('MM/DD(ddd)') }}</td>

                    {{-- 出勤時刻 --}}
                    <td>{{ $record && $record->check_in ? \Carbon\Carbon::parse($record->check_in)->format('H:i') : '-' }}</td>

                    {{-- 退勤時刻 --}}
                    <td>{{ $record && $record->check_out ? \Carbon\Carbon::parse($record->check_out)->format('H:i') : '-' }}</td>

                    {{-- 💡 休憩（合計時間）：分単位の整数を「H:i」形式に変換して表示 --}}
                    <td>
                        @if($record && $record->total_break_time !== null)
                        {{ sprintf('%02d:%02d', floor($record->total_break_time / 60), $record->total_break_time % 60) }}
                        @else
                        -
                        @endif
                    </td>

                    {{-- 💡 合計（実働時間）：コントローラで計算済みの値をそのまま表示 --}}
                    <td>
                        {{ $record->actual_working_hours ?? '-' }}
                    </td>

                    {{-- 詳細リンク --}}
                    <td>
                        <a href="{{ route('attendance.detail', ['date' => $day->format('Y-m-d')]) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

@endsection