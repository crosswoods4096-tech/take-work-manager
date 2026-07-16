<header>
    <div class="header-bar">

        {{-- 左：ロゴ --}}
        <div class="header-left">
            {{-- 管理者と一般ユーザーでロゴの遷移先を出し分け --}}
            @auth
            @if(auth()->user()->role === 'admin')
            <a href="{{ route('admin.attendance.daily') }}">
                @else
                <a href="{{ route('attendance.register') }}">
                    @endif
                    @else
                    <a href="/">
                        @endauth
                        <img src="{{ asset('storage/coachtech-logo.png') }}" alt="COACHTECH" class="header-logo">
                    </a>
        </div>

        {{-- 右：ナビゲーションリンク --}}
        <div class="header-right">
            @auth
            @if(auth()->user()->role === 'admin')
            {{-- 👑 管理者用のナビゲーションリンク --}}
            <a href="{{ route('admin.attendance.daily') }}" class="header-link">勤怠一覧</a>
            <a href="{{ route('admin.staff.index') }}" class="header-link">スタッフ一覧</a>
            <a href="{{ route('admin.application.index') }}" class="header-link">申請一覧</a>
            @else
            {{-- 👤 一般ユーザー用のナビゲーションリンク --}}
            <a href="{{ route('attendance.register') }}" class="header-link">勤怠</a>
            <a href="{{ route('attendance.index') }}" class="header-link">勤怠一覧</a>
            <a href="{{ route('applicate.index') }}" class="header-link">申請</a>
            <a href="{{ route('reports.index') }}" class="header-link">レポート</a>
            @endif

            {{-- 🚪 共通：ログアウト（POST送信） --}}
            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="logout-btn" style="background:none;border:none;color:#fff;cursor:pointer;font-size:inherit;padding:0;font-weight:bold;margin-left:20px;">
                    ログアウト
                </button>
            </form>
            @endauth
        </div>

    </div>
</header>