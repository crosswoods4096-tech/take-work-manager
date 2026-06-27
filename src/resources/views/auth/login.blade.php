@extends('layouts.easyapp')

@section('content')
<div class="container d-flex justify-content-center">
    <div class="w-100" style="max-width: 420px;">

        {{-- タイトル --}}
        <h2 style="margin-top: 20px; text-align:center; font-weight:bold;">
            ログイン
        </h2>

        {{-- 入力フォーム --}}
        <form action="/login" method="POST" novalidate>
            @csrf

            {{-- メールアドレス --}}
            <div class="mb-3">
                <label for="email" class="form-label">メールアドレス</label>
                <input type="email" name="email" id="email" class="form-control"
                    value="{{ old('email') }}">
                @error('email')
                <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            {{-- パスワード --}}
            <div class="mb-4">
                <label for="password" class="form-label">パスワード</label>
                <input type="password" name="password" id="password" class="form-control">
                @error('password')
                <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            {{-- ログインボタン --}}
            <div class="d-grid mb-3">
                <button class="btn px-4"
                    style="background-color: #000000; color: #fff; font-weight: bold;">
                    ログイン
                </button>
            </div>

            {{-- 会員登録へのリンク --}}
            <div class="text-center">
                <a href="{{ route('register') }}" class="register-link" style="font-size: 0.9rem;">
                    会員登録はこちら
                </a>
            </div>

        </form>
    </div>
</div>
@endsection