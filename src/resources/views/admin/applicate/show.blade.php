@extends('layouts.app')

@section('content')
<div class="admin-app-detail" style="padding: 20px; max-width: 800px; margin: 0 auto;">

    <h2 style="margin-bottom: 20px; font-size: 1.5rem; font-weight: bold;">申請内容の確認</h2>

    <div style="background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px; text-align: left;">
            <tr style="border-bottom: 1px solid #eee;">
                <th style="padding: 15px; width: 180px; color: #555;">申請者</th>
                <td style="padding: 15px; font-weight: bold;">{{ $application->user->name }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #eee;">
                <th style="padding: 15px; color: #555;">対象日</th>
                <td style="padding: 15px; font-weight: bold;">{{ \Carbon\Carbon::parse($application->application_date)->format('Y年m月d日') }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #eee;">
                <th style="padding: 15px; color: #555;">修正希望時間</th>
                <td style="padding: 15px; font-size: 1.1rem;">
                    <strong>出勤:</strong> {{ \Carbon\Carbon::parse($application->requested_check_in)->format('H:i') }} <br>
                    <strong>退勤:</strong> {{ \Carbon\Carbon::parse($application->requested_check_out)->format('H:i') }}
                </td>
            </tr>
            <tr style="border-bottom: 1px solid #eee;">
                <th style="padding: 15px; color: #555;">希望休憩時間</th>
                <td style="padding: 15px; font-size: 0.95rem; color: #555;">
                    @forelse($application->applicationRests as $index => $rest)
                    <div>休憩{{ $index + 1 }}: {{ \Carbon\Carbon::parse($rest->start_time)->format('H:i') }} 〜 {{ \Carbon\Carbon::parse($rest->end_time)->format('H:i') }}</div>
                    @empty
                    <div>休憩なし</div>
                    @endforelse
                </td>
            </tr>
            <tr style="border-bottom: 1px solid #eee;">
                <th style="padding: 15px; color: #555;">申請理由</th>
                <td style="padding: 15px; background: #fafafa; color: #333; border-radius: 4px;">{{ $application->reason }}</td>
            </tr>
        </table>

        {{-- アクションボタン --}}
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <a href="{{ route('admin.application.index') }}" style="text-decoration: none; color: #666;">一覧に戻る</a>

            @if($application->status === 'pending')
            <div style="display: flex; gap: 15px;">
                {{-- 却下フォーム --}}
                <form action="{{ route('admin.application.reject', ['id' => $application->id]) }}" method="POST">
                    @csrf
                    <button type="submit" onclick="return confirm('本当にこの申請を却下しますか？')" style="background: #dc3545; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold;">
                        申請を却下する
                    </button>
                </form>

                {{-- 承認フォーム --}}
                <form action="{{ route('admin.application.approve', ['id' => $application->id]) }}" method="POST">
                    @csrf
                    <button type="submit" onclick="return confirm('申請を承認し、勤怠データを書き換えます。よろしいですか？')" style="background: #28a745; color: #fff; border: none; padding: 10px 25px; border-radius: 4px; cursor: pointer; font-weight: bold;">
                        申請を承認する
                    </button>
                </form>
            </div>
            @else
            <div style="font-weight: bold; color: #777;">
                ※この申請は処理済みです（ステータス: {{ $application->status }}）
            </div>
            @endif
        </div>
    </div>
</div>
@endsection