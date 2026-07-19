@extends('layouts.app')

@section('content')
<div style="max-width: 600px; margin: 60px auto; padding: 30px; background: #fff; border: 1px solid #eee; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center; font-family: sans-serif;">

    <h2 style="font-size: 1.5rem; font-weight: bold; margin-bottom: 20px; color: #333;">会員登録の完了まであと一歩です</h2>

    <p style="color: #666; line-height: 1.6; margin-bottom: 25px;">
        ご登録いただいたメールアドレスに確認用のリンクを送信しました。<br>
        メール内のリンクをクリックして、登録を完了させてください。
    </p>

    @if (session('status') == 'verification-link-sent')
    <div style="background: #e6fffa; border: 1px solid #38b2ac; color: #234e52; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem;">
        🟢 新しい認証リンクを再送しました！
    </div>
    @endif

    <div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
        <p style="font-size: 0.85rem; color: #999; margin-bottom: 15px;">もしメールが届かない場合は、以下のボタンから再送できます。</p>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" style="background: #000; color: #fff; border: none; padding: 10px 20px; font-size: 0.9rem; font-weight: bold; border-radius: 4px; cursor: pointer;">
                認証メールを再送する
            </button>
        </form>
    </div>

</div>
@endsection