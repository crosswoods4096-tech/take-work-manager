@extends('layouts.easyapp')

@section('content')
<div class="container" style="padding: 40px 20px; text-align: center;">
    <h2 style="font-weight: bold; margin-bottom: 20px;">｜レポート</h2>
    <div style="background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); max-width: 600px; margin: 0 auto;">
        <p style="font-size: 1.2rem; color: #555;">レポート機能は現在準備中です。</p>
        <p style="color: #888; margin-top: 10px;">今後のアップデートで、月ごとの稼働統計やグラフが表示されるようになります。</p>
        <a href="{{ route('attendance.register') }}" style="display: inline-block; margin-top: 20px; color: #000; font-weight: bold;">打刻画面に戻る</a>
    </div>
</div>
@endsection