<header>
    <div class="header-bar">

        {{-- 左：ロゴ（そのまま維持） --}}
        <div class="header-left">
            <a href="/">
                <img src="{{ asset('storage/coachtech-logo.png') }}" alt="COACHTECH" class="header-logo">
            </a>
        </div>

        {{-- 右：勤怠管理用のナビゲーションリンク（5つ） --}}
        <div class="header-right">
            {{-- ログイン中のみ表示させる場合は、全体を @auth 〜 @endauth で囲んでください --}}
            @auth
            <a href="{{ route('attendance.register') }}" class="header-link">勤怠</a>
            <a href="{{ route('attendance.index') }}" class="header-link">勤怠一覧</a>
            <!-- <a href="{{ route('applicate.index') }}" class="header-link">申請</a> -->
            <!-- <a href="{{ route('reports.index') }}" class="header-link">レポート</a> -->

            {{-- ログアウト（POST送信） --}}
            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="logout-btn" style="background:none;border:none;color:#fff;cursor:pointer;font-size:inherit;padding:0;">
                    ログアウト
                </button>
            </form>
            @endauth
        </div>

    </div>
</header>