@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/applicate/index.css') }}">
@endsection

@section('content')

<div class="application-container">
    <div class="application-list-wrapper">

        {{-- 1. タイトル（左寄せ） --}}
        <h2 class="page-title">｜申請一覧</h2>

        {{-- 2. 承認待ち・承認済み切り替えタブ --}}
        <div class="tab-navigation">
            <a href="{{ route('applications.index', ['tab' => 'pending']) }}"
                class="tab-item {{ $activeTab === 'pending' ? 'active' : '' }}">
                承認待ち
            </a>
            <a href="{{ route('applications.index', ['tab' => 'approved']) }}"
                class="tab-item {{ $activeTab === 'approved' ? 'active' : '' }}">
                承認済み
            </a>
        </div>

        {{-- タブの下の区切り下線 --}}
        <hr class="tab-divider">

        {{-- 3. 申請一覧テーブル --}}
        <div class="table-responsive">
            <table class="application-table">
                <thead>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $app)
                    <tr>
                        {{-- 状態（バッジ形式で色分け可能に） --}}
                        <td>
                            <span class="status-badge badge-{{ $app->status }}">
                                @if($app->status === 'pending') 承認待ち
                                @elseif($app->status === 'approved') 承認済み
                                @elseif($app->status === 'rejected') 却下
                                @endif
                            </span>
                        </td>

                        {{-- 名前 --}}
                        <td>{{ $app->user->name }}</td>

                        {{-- 対象日時 --}}
                        <td class="col-date">{{ \Carbon\Carbon::parse($app->target_date)->isoFormat('YYYY/MM/DD(ddd)') }}</td>

                        {{-- 申請理由 --}}
                        <td class="col-reason">{{ $app->reason }}</td>

                        {{-- 申請日時 --}}
                        <td>{{ $app->created_at->format('Y/m/d H:i') }}</td>

                        {{-- 詳細リンク（その日付の勤怠詳細画面へ遷移） --}}
                        <td>
                            <a href="{{ route('attendance.detail', ['date' => $app->target_date]) }}" class="detail-link">詳細</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="empty-message">該当する申請はありません。</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>