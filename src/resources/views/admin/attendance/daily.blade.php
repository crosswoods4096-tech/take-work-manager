@extends('layouts.app')

@section('content')
<div class="admin-container" style="padding: 20px; max-width: 1200px; margin: 0 auto;">

    {{-- 💡 1. ログアウトボタン（動作確認用） --}}
    <div style="text-align: right; margin-bottom: 10px;">
        <form action="{{ route('admin.logout') }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" style="background: #666; color: #fff; border: none; padding: 5px 15px; border-radius: 4px; cursor: pointer; font-size: 0.9em;">
                管理者ログアウト
            </button>
        </form>
    </div>

    {{-- 💡 2. 日付切り替えヘッダー --}}
    <div class="date-switcher" style="display: flex; justify-content: center; align-items: center; gap: 20px; margin-bottom: 30px;">
        <a href="{{ route('admin.attendance.daily', ['date' => $prevDate]) }}" style="text-decoration: none; color: #333; font-size: 1.5rem;">◀</a>
        <h2 style="margin: 0; font-size: 1.5rem;">
            {{ $currentDate->format('Y年m月d日') }} ({{ $currentDate->isoFormat('ddd') }})
        </h2>
        <a href="{{ route('admin.attendance.daily', ['date' => $nextDate]) }}" style="text-decoration: none; color: #333; font-size: 1.5rem;">▶</a>
    </div>

    {{-- 💡 3. 日次勤怠テーブル --}}
    <div class="table-wrapper" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background-color: #f5f5f5; border-bottom: 1px solid #ddd;">
                    <th style="padding: 12px 15px;">名前</th>
                    <th style="padding: 12px 15px;">出勤時間</th>
                    <th style="padding: 12px 15px;">退勤時間</th>
                    <th style="padding: 12px 15px;">休憩時間（回数）</th>
                    <th style="padding: 12px 15px;">詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                @php
                // その日の勤怠データを1件取得
                $attendance = $user->attendances->first();
                @endphp
                <tr style="border-bottom: 1px solid #eee;">
                    {{-- 名前 --}}
                    <td style="padding: 12px 15px; font-weight: bold;">{{ $user->name }}</td>

                    {{-- 出勤時間 --}}
                    <td style="padding: 12px 15px;">
                        {{ $attendance && $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : 'ー' }}
                    </td>

                    {{-- 退勤時間 --}}
                    <td style="padding: 12px 15px;">
                        {{ $attendance && $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : 'ー' }}
                    </td>

                    {{-- 休憩時間（複数休憩の並びを綺麗に表示） --}}
                    <td style="padding: 12px 15px; font-size: 0.9em; color: #555;">
                        @if ($attendance && $attendance->rests->isNotEmpty())
                        <div style="display: flex; flex-direction: column; gap: 2px;">
                            @foreach ($attendance->rests as $index => $rest)
                            <span>
                                休憩{{ $index + 1 }}:
                                {{ \Carbon\Carbon::parse($rest->start_time)->format('H:i') }} 〜
                                {{ $rest->end_time ? \Carbon\Carbon::parse($rest->end_time)->format('H:i') : '打刻中' }}
                            </span>
                            @endforeach
                        </div>
                        @else
                        ー
                        @endif
                    </td>

                    {{-- 詳細リンク（のちのステップ③で使います） --}}
                    <td style="padding: 12px 15px;">
                        <a href="#" style="color: #007bff; text-decoration: none; font-size: 0.9em;">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection