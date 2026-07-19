@extends('layouts.app')

@section('content')
<div class="admin-detail-container" style="padding: 20px; max-width: 800px; margin: 0 auto;">

    <h2 style="margin-bottom: 20px; font-size: 1.5rem; font-weight: bold;">勤怠詳細（管理者修正）</h2>
    {{-- ➕ 以下のエラーメッセージ表示エリアを追加 --}}
    @if ($errors->any())
    <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
        <ul style="margin: 0; padding-left: 20px;">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div style="background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">


        <form action="{{ route('admin.attendance.update', ['id' => $attendance->id]) }}" method="POST">
            @csrf

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; text-align: left;">
                {{-- 名前 --}}
                <tr style="border-bottom: 1px solid #eee;">
                    <th style="padding: 15px; width: 200px; color: #555;">名前</th>
                    <td style="padding: 15px; font-weight: bold; font-size: 1.1rem;">{{ $attendance->user->name }}</td>
                </tr>

                {{-- 日付 --}}
                <tr style="border-bottom: 1px solid #eee;">
                    <th style="padding: 15px; color: #555;">日付</th>
                    <td style="padding: 15px;">{{ \Carbon\Carbon::parse($attendance->date)->format('Y年m月d日') }}</td>
                </tr>

                {{-- 出勤・退勤 --}}
                <tr style="border-bottom: 1px solid #eee;">
                    <th style="padding: 15px; color: #555;">出勤・退勤</th>
                    <td style="padding: 15px;">
                        <input type="time" name="check_in" value="{{ old('check_in', \Carbon\Carbon::parse($attendance->check_in)->format('H:i')) }}" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        〜
                        <input type="time" name="check_out" value="{{ old('check_out', \Carbon\Carbon::parse($attendance->check_out)->format('H:i')) }}" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    </td>
                </tr>

                {{-- 既存の休憩ループ --}}
                @foreach ($attendance->rests as $index => $rest)
                <tr style="border-bottom: 1px solid #eee;">
                    <th style="padding: 15px; color: #555;">休憩{{ $index + 1 }}</th>
                    <td style="padding: 15px;">
                        <input type="time" name="rests[{{ $rest->id }}][start_time]" value="{{ old('rests.'.$rest->id.'.start_time', \Carbon\Carbon::parse($rest->start_time)->format('H:i')) }}" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        〜
                        <input type="time" name="rests[{{ $rest->id }}][end_time]" value="{{ old('rests.'.$rest->id.'.end_time', $rest->end_time ? \Carbon\Carbon::parse($rest->end_time)->format('H:i') : '') }}" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    </td>
                </tr>
                @endforeach

                {{-- ➕ 休憩の追加枠 --}}
                <tr style="border-bottom: 1px solid #eee; background-color: #fafafa;">
                    <th style="padding: 15px; color: #666; font-weight: normal;">➕ 休憩を追加</th>
                    <td style="padding: 15px;">
                        <input type="time" name="rests[new][start_time]" value="{{ old('rests.new.start_time') }}" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        〜
                        <input type="time" name="rests[new][end_time]" value="{{ old('rests.new.end_time') }}" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    </td>
                </tr>
            </table>

            <div style="text-align: right; margin-top: 20px;">
                <a href="{{ route('admin.attendance.daily', ['date' => $attendance->date]) }}" style="text-decoration: none; color: #666; margin-right: 20px;">戻る</a>
                <button type="submit" style="background: #007bff; color: #fff; border: none; padding: 10px 25px; border-radius: 4px; cursor: pointer; font-weight: bold;">
                    変更を保存する
                </button>
            </div>
        </form>
    </div>
</div>
@endsection