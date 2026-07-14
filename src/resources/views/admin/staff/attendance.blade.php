@extends('layouts.app')

@section('content')
<div class="admin-monthly-container" style="padding: 20px; max-width: 1200px; margin: 0 auto;">

    {{-- ヘッダー --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0; font-size: 1.5rem; font-weight: bold;">{{ $staff->name }} さんの勤怠</h2>
        <a href="{{ route('admin.staff.index') }}" style="text-decoration: none; color: #007bff; font-size: 0.9em;">← スタッフ一覧に戻る</a>
    </div>

    {{-- 月移動ナビゲーション --}}
    <div class="month-navigation" style="display: flex; justify-content: center; align-items: center; gap: 20px; margin-bottom: 30px;">
        <a href="{{ route('admin.staff.attendance', ['id' => $staff->id, 'month' => $prevMonth]) }}" style="text-decoration: none; color: #333; font-size: 1.1rem;">&lt; 先月</a>
        <span class="current-month" style="font-size: 1.3rem; font-weight: bold;">{{ $currentMonth->format('Y年m月') }}</span>
        <a href="{{ route('admin.staff.attendance', ['id' => $staff->id, 'month' => $nextMonth]) }}" style="text-decoration: none; color: #333; font-size: 1.1rem;">次月 &gt;</a>
    </div>

    {{-- 月次勤怠テーブル --}}
    <div class="table-wrapper" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background-color: #f5f5f5; border-bottom: 1px solid #ddd;">
                    <th style="padding: 12px 15px;">日付</th>
                    <th style="padding: 12px 15px;">出勤</th>
                    <th style="padding: 12px 15px;">退勤</th>
                    <th style="padding: 12px 15px;">休憩</th>
                    <th style="padding: 12px 15px;">合計（実働）</th>
                    <th style="padding: 12px 15px; text-align: center; width: 100px;">詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($daysInMonth as $day)
                @php
                $record = $attendances->get($day->format('Y-m-d'));
                @endphp
                <tr style="border-bottom: 1px solid #eee;">
                    {{-- 日付 --}}
                    <td style="padding: 12px 15px; color: #555;">{{ $day->isoFormat('MM/DD(ddd)') }}</td>

                    {{-- 出勤 --}}
                    <td style="padding: 12px 15px;">{{ $record && $record->check_in ? \Carbon\Carbon::parse($record->check_in)->format('H:i') : '-' }}</td>

                    {{-- 退勤 --}}
                    <td style="padding: 12px 15px;">{{ $record && $record->check_out ? \Carbon\Carbon::parse($record->check_out)->format('H:i') : '-' }}</td>

                    {{-- 休憩 --}}
                    <td style="padding: 12px 15px;">
                        @if ($record && $record->total_break_time !== null)
                        {{ sprintf('%02d:%02d', floor($record->total_break_time / 60), $record->total_break_time % 60) }}
                        @else
                        -
                        @endif
                    </td>

                    {{-- 実働時間 --}}
                    <td style="padding: 12px 15px; font-weight: bold;">
                        {{ $record->actual_working_hours ?? '-' }}
                    </td>

                    {{-- 詳細リンク（管理者用の個別修正へジャンプ） --}}
                    <td style="padding: 12px 15px; text-align: center;">
                        @if ($record)
                        <a href="{{ route('admin.attendance.detail', ['id' => $record->id]) }}" style="color: #007bff; text-decoration: none; font-size: 0.9em; font-weight: bold;">詳細</a>
                        @else
                        <span style="color: #ccc; font-size: 0.9em;">-</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection