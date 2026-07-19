<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者ログイン</title>
</head>

<body style="background-color: #f5f5f5; font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0;">

    <div style="background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 100%; max-width: 400px;">
        <h2 style="text-align: center; margin-bottom: 30px; color: #333;">管理者ログイン</h2>

        {{-- エラーメッセージの表示 --}}
        @if ($errors->any())
        <div style="background: #fde8e8; color: red; padding: 10px; border-radius: 4px; margin-bottom: 20px; font-size: 0.9em;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('admin.login.submit') }}" method="POST" novalidate>
            @csrf
            {{-- 💡 「ログイン情報が登録されていません」のエラー表示 --}}
            @error('login_error')
            <div class="alert alert-danger" style="color: red; margin-bottom: 15px;">
                {{ $message }}
            </div>
            @enderror

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; color: #666; font-size: 0.9em;">メールアドレス</label>
                <input type="email" name="email" value="{{ old('email') }}" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                {{-- 💡 「メールアドレスを入力してください」のエラー表示 --}}
                @error('email')
                <span style="color: red; font-size: 0.85rem;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 25px;">
                <label style="display: block; margin-bottom: 5px; color: #666; font-size: 0.9em;">パスワード</label>
                <input type="password" name="password" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                {{-- 💡 「パスワードを入力してください」のエラー表示 --}}
                @error('password')
                <span style="color: red; font-size: 0.85rem;">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" style="width: 100%; padding: 12px; background: #333; color: #fff; border: none; border-radius: 4px; font-size: 1rem; cursor: pointer;">
                ログイン
            </button>
        </form>
    </div>

</body>

</html>