@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/detail.css') }}">
@endsection

@section('content')

<div class="attendance-container">
    <div class="attendance-detail-wrapper">

        {{-- 1. タイトル（左寄せ） --}}
        <h2 class="page-title">勤怠詳細</h2>

        {{-- 修正申請フォーム（ボタンを押したらPOST送信する構造にします） --}}
        <form action="{{ route('attendance.update', ['date' => $date]) }}" method="POST">
            @csrf

            <table class="detail-table">
                {{-- 名前 --}}
                <tr>
                    <th>名前</th>
                    <td>
                        <span class="user-name">{{ $user->name }}</span>
                    </td>
                </tr>

                {{-- 日付 --}}
                <tr>
                    <th>日付</th>
                    <td>
                        <div class="date-display">
                            <span class="date-year">{{ \Carbon\Carbon::parse($date)->format('Y年') }}</span>
                            <span class="date-day">{{ \Carbon\Carbon::parse($date)->isoFormat('M月D日(ddd)') }}</span>
                        </div>
                    </td>
                </tr>

                {{-- 出勤・退勤 --}}
                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <div class="time-range-group">
                            <input type="time" name="check_in" class="time-input"
                                value="{{ $attendance && $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '' }}">
                            <span class="range-separator">～</span>
                            <input type="time" name="check_out" class="time-input"
                                value="{{ $attendance && $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '' }}">
                        </div>
                    </td>
                </tr>

                {{-- 休憩1 --}}
                <tr>
                    <th>休憩</th>
                    <td>
                        <div class="time-range-group">
                            <input type="time" name="rest_start_1" class="time-input"
                                value="{{ isset($rests[0]) ? \Carbon\Carbon::parse($rests[0]->start_time)->format('H:i') : '' }}">
                            <span class="range-separator">～</span>
                            <input type="time" name="rest_end_1" class="time-input"
                                value="{{ isset($rests[0]->end_time) ? \Carbon\Carbon::parse($rests[0]->end_time)->format('H:i') : '' }}">
                        </div>
                    </td>
                </tr>

                {{-- 休憩2 --}}
                <tr>
                    <th>休憩2</th>
                    <td>
                        <div class="time-range-group">
                            <input type="time" name="rest_start_2" class="time-input"
                                value="{{ isset($rests[1]) ? \Carbon\Carbon::parse($rests[1]->start_time)->format('H:i') : '' }}">
                            <span class="range-separator">～</span>
                            <input type="time" name="rest_end_2" class="time-input"
                                value="{{ isset($rests[1]->end_time) ? \Carbon\Carbon::parse($rests[1]->end_time)->format('H:i') : '' }}">
                        </div>
                    </td>
                </tr>

                {{-- 備考（ボックス形式） --}}
                <tr>
                    <th>備考</th>
                    <td>
                        <textarea name="remarks" class="remarks-textarea" rows="4" placeholder="修正理由や備考を入力してください">{{ $attendance->remarks ?? '' }}</textarea>
                    </td>
                </tr>
            </table>

            {{-- 右下に修正ボタン（黒地に白） --}}
            <div class="form-actions">
                <button type="submit" class="btn-submit">修正</button>
            </div>

        </form>
    </div>
</div>
@endsection