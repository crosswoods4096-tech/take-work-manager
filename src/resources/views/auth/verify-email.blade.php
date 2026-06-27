@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center">
    <div class="w-100 text-center" style="max-width: 480px; margin-top: 80px;">

        {{-- タイトル --}}
        <h2 class="mb-4">メール認証のお願い</h2>

        {{-- 誘導メッセージ --}}
        <p class="mb-4">
            会員登録ありがとうございます。<br>
            ご登録のメールアドレス宛に認証メールを送信しました。<br>
            認証を完了してからログインを行ってください。
        </p>

        {{-- 認証はこちらからボタン --}}
        <div class="d-grid mb-3">
            <a href="http://localhost:8025/" class="btn btn-primary py-2">
                認証はこちらから
            </a>
        </div>

        {{-- 再送リンク --}}
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-link text-muted" style="font-size: 0.9rem;">
                認証メールを再送する
            </button>
        </form>

        {{-- メッセージ表示 --}}
        @if (session('message'))
        <p class="text-success mt-3">{{ session('message') }}</p>
        @endif

    </div>
</div>
@endsection