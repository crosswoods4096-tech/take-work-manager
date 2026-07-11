@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/detail.css') }}">
@endsection

@section('content')

<div class="attendance-container">
    <div class="attendance-detail-wrapper">

        {{-- 1. タイトル（左寄せ） --}}
        <h2 class="page-title">勤怠詳細</h2>

        {{-- 💡 追記：コントローラからのカスタムエラーメッセージ（session('error')）を表示する --}}
        @if (session('error'))
        <div class="alert alert-danger" style="color: red; background-color: #fde8e8; padding: 10px; margin-bottom: 15px; border-radius: 4px; border: 1px solid #f8b4b4;">
            {{ session('error') }}
        </div>
        @endif

        {{-- 💡 参考：通常のバリデーションエラー（入力不備など）も表示したい場合はこちらも便利です --}}
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

                {{-- 💡 休憩（ループ処理で数に関わらずすべて自動表示） --}}
                @foreach ($rests as $index => $rest)
                <tr>
                    {{-- 最初の休憩だけ「休憩」、2回目以降は「休憩2」「休憩3」と表示を切り替えるおまけ付き --}}
                    <th>{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</th>
                    <td>
                        <div class="time-range-group">
                            {{-- 💡 old() を追加して、エラー時も入力が残るようにしています --}}
                            <input type="time" name="rests[{{ $rest->id }}][start_time]" class="time-input"
                                value="{{ old('rests.'.$rest->id.'.start_time', $rest->start_time ? \Carbon\Carbon::parse($rest->start_time)->format('H:i') : '') }}">
                            <span class="range-separator">～</span>
                            <input type="time" name="rests[{{ $rest->id }}][end_time]" class="time-input"
                                value="{{ old('rests.'.$rest->id.'.end_time', $rest->end_time ? \Carbon\Carbon::parse($rest->end_time)->format('H:i') : '') }}">
                        </div>
                    </td>
                </tr>
                @endforeach

                {{-- 💡 ここを追記：数が増える時のための「新しい休憩枠」を1つ余分に用意 --}}
                <tr class="new-rest-row" style="background-color: #fafafa;">
                    <th>
                        <span style="font-size: 0.9em; color: #666; font-weight: normal;">➕ 休憩を追加</span>
                    </th>
                    <td>
                        <div class="time-range-group">
                            {{-- 新規追加分は、IDの代わりに 'new' という固定のキーで送信します --}}
                            <input type="time" name="rests[new][start_time]" class="time-input"
                                value="{{ old('rests.new.start_time') }}">
                            <span class="range-separator">～</span>
                            <input type="time" name="rests[new][end_time]" class="time-input"
                                value="{{ old('rests.new.end_time') }}">
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