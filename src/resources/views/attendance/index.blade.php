@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/index.css') }}">
@endsection

@section('content')

{{-- 全体の背景を薄いグレーにするためコンテナで囲みます --}}
<div class="attendance-container">
    <div class="attendance-list-wrapper">

        {{-- 1. タイトル（左寄せ） --}}
        <h2 class="page-title">勤怠一覧</h2>

        {{-- 2. 月移動ナビゲーション（中央に年月、左右にリンク） --}}
        <div class="month-navigation">
            <a href="{{ route('attendance.index', ['month' => $prevMonth]) }}" class="nav-link">&lt; 先月</a>
            <span class="current-month">{{ $currentMonth->format('Y年m月') }}</span>
            <a href="{{ route('attendance.index', ['month' => $nextMonth]) }}" class="nav-link">次月 &gt;</a>
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

                        {{-- 休憩（合計時間） --}}
                        <td>{{ $record && $record->total_break_time ? \Carbon\Carbon::parse($record->total_break_time)->format('H:i') : '-' }}</td>

                        {{-- 合計（実働時間） --}}
                        <td>
                            @if($record && $record->total_working_hours)
                            {{-- 実働時間は、後ほどコントローラーやモデル側で計算した値を表示する想定です --}}
                            {{ $record->actual_working_hours ?? '-' }}
                            @else
                            -
                            @endif
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
</div>
@endsection