@extends('layouts.app')

@section('content')
<div class="admin-app-container" style="padding: 20px; max-width: 1200px; margin: 0 auto;">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0; font-size: 1.5rem; font-weight: bold;">修正申請一覧</h2>
        <a href="{{ route('admin.attendance.daily') }}" style="text-decoration: none; color: #007bff; font-size: 0.9em;">← 日次勤怠一覧に戻る</a>
    </div>

    {{-- トーストメッセージ --}}
    @if (session('success'))
    <div style="color: green; background-color: #e6f4ea; padding: 10px; margin-bottom: 20px; border-radius: 4px; border: 1px solid #b7e1cd;">
        {{ session('success') }}
    </div>
    @endif

    {{-- タブ切り替え --}}
    <div class="tab-group" style="display: flex; border-bottom: 2px solid #ddd; margin-bottom: 20px;">

        {{-- 💡 「承認待ち」タブ --}}
        @if ($activeTab === 'pending')
        {{-- アクティブな状態 --}}
        <a href="{{ route('admin.application.index', ['tab' => 'pending']) }}"
            style="padding: 10px 20px; text-decoration: none; color: #000; border-bottom: 3px solid #000; font-weight: bold;">
            承認待ち
        </a>
        @else
        {{-- 非アクティブな状態 --}}
        <a href="{{ route('admin.application.index', ['tab' => 'pending']) }}"
            style="padding: 10px 20px; text-decoration: none; color: #666; border-bottom: 3px solid transparent; font-weight: normal;">
            承認待ち
        </a>
        @endif

        {{-- 💡 「処理済み」タブ --}}
        @if ($activeTab === 'approved')
        {{-- アクティブな状態 --}}
        <a href="{{ route('admin.application.index', ['tab' => 'approved']) }}"
            style="padding: 10px 20px; text-decoration: none; color: #000; border-bottom: 3px solid #000; font-weight: bold;">
            処理済み（承認・却下）
        </a>
        @else
        {{-- 非アクティブな状態 --}}
        <a href="{{ route('admin.application.index', ['tab' => 'approved']) }}"
            style="padding: 10px 20px; text-decoration: none; color: #666; border-bottom: 3px solid transparent; font-weight: normal;">
            処理済み（承認・却下）
        </a>
        @endif

    </div>

    {{-- テーブル --}}
    <div class="table-wrapper" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background-color: #f5f5f5; border-bottom: 1px solid #ddd;">
                    <th style="padding: 12px 15px;">状態</th>
                    <th style="padding: 12px 15px;">申請者</th>
                    <th style="padding: 12px 15px;">対象日</th>
                    <th style="padding: 12px 15px;">申請理由</th>
                    <th style="padding: 12px 15px;">申請日時</th>
                    <th style="padding: 12px 15px; text-align: center;">詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($applications as $app)
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px 15px;">
                        @if($app->status === 'pending')
                        <span style="background: #fff3cd; color: #856404; padding: 3px 8px; border-radius: 4px; font-size: 0.85em;">承認待ち</span>
                        @elseif($app->status === 'approved')
                        <span style="background: #d4edda; color: #155724; padding: 3px 8px; border-radius: 4px; font-size: 0.85em;">承認済み</span>
                        @else
                        <span style="background: #f8d7da; color: #721c24; padding: 3px 8px; border-radius: 4px; font-size: 0.85em;">却下</span>
                        @endif
                    </td>
                    <td style="padding: 12px 15px; font-weight: bold;">{{ $app->user->name }}</td>
                    <td style="padding: 12px 15px;">{{ \Carbon\Carbon::parse($app->application_date)->format('Y/m/d') }}</td>
                    <td style="padding: 12px 15px; color: #555; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $app->reason }}</td>
                    <td style="padding: 12px 15px; color: #777; font-size: 0.9em;">{{ $app->created_at->format('Y/m/d H:i') }}</td>
                    <td style="padding: 12px 15px; text-align: center;">
                        <a href="{{ route('admin.application.show', ['id' => $app->id]) }}" style="color: #007bff; text-decoration: none; font-size: 0.9em; font-weight: bold;">確認</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding: 30px; text-align: center; color: #999;">該当する申請はありません。</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection