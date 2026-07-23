@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/detail.css') }}">
@endsection

@section('content')

<div class="attendance-container">
    <div class="attendance-detail-wrapper">

        {{-- 1. タイトル（左寄せ） --}}
        <h2 class="page-title">勤怠詳細</h2>

        {{-- 💡 コントローラからのカスタムエラーメッセージ（session('error')）を表示 --}}
        @if (session('error'))
        <div class="alert alert-danger" style="color: red; background-color: #fde8e8; padding: 10px; margin-bottom: 15px; border-radius: 4px; border: 1px solid #f8b4b4;">
            {{ session('error') }}
        </div>
        @endif

        {{-- 💡 バリデーションエラー表示 --}}
        @if ($errors->any())
        <div class="alert alert-danger" style="color: red; background-color: #fde8e8; padding: 10px; margin-bottom: 15px; border-radius: 4px; border: 1px solid #f8b4b4;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- 修正申請フォーム --}}
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
                            <span class="date-day">{{ \Carbon\Carbon::parse($date)->isoFormat('M月D日') }}</span>
                        </div>
                    </td>
                </tr>

                {{-- 出勤・退勤 --}}
                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <div class="time-range-group">
                            <input type="time" name="check_in" class="time-input"
                                value="{{ old('check_in', $attendance && $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '') }}">
                            <span class="range-separator">～</span>
                            <input type="time" name="check_out" class="time-input"
                                value="{{ old('check_out', $attendance && $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '') }}">
                        </div>
                    </td>
                </tr>

                {{-- 💡 休憩（既存の休憩をループ表示） --}}
                @if($attendance && $attendance->rests)
                @foreach($attendance->rests as $index => $rest)
                <tr>
                    <th>
                        @if($loop->first)
                        休憩
                        @else
                        休憩{{ $loop->iteration }}
                        @endif
                    </th>
                    <td>
                        <div class="time-range-group">
                            <input type="time" name="rests[{{ $index }}][start_time]" class="time-input"
                                value="{{ old("rests.{$index}.start_time", $rest->start_time ? \Carbon\Carbon::parse($rest->start_time)->format('H:i') : '') }}">
                            <span class="range-separator">～</span>
                            <input type="time" name="rests[{{ $index }}][end_time]" class="time-input"
                                value="{{ old("rests.{$index}.end_time", $rest->end_time ? \Carbon\Carbon::parse($rest->end_time)->format('H:i') : '') }}">
                        </div>
                    </td>
                </tr>
                @endforeach
                @endif

                {{-- 💡 新規追加用の休憩枠（1行分追加） --}}
                @php
                $nextRestNumber = ($attendance && $attendance->rests) ? count($attendance->rests) + 1 : 1;
                $labelName = ($nextRestNumber === 1) ? '休憩' : '休憩' . $nextRestNumber;
                @endphp
                <tr>
                    <th>{{ $labelName }}</th>
                    <td>
                        <div class="time-range-group">
                            <input type="time" name="new_rest_start" class="time-input" value="{{ old('new_rest_start') }}">
                            <span class="range-separator">～</span>
                            <input type="time" name="new_rest_end" class="time-input" value="{{ old('new_rest_end') }}">
                        </div>
                    </td>
                </tr>

                {{-- 備考（ボックス形式） --}}
                <tr>
                    <th>備考</th>
                    <td>
                        <textarea name="remarks" class="remarks-textarea" rows="4" placeholder="修正理由や備考を入力してください">{{ old('remarks', $attendance->remarks ?? '') }}</textarea>
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