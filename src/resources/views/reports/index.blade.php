@extends('layouts.app')

@section('content')
<div class="report-container" style="max-width: 1000px; margin: 0 auto; padding: 30px 20px; font-family: 'Helvetica Neue', Arial, sans-serif; color: #333;">

    {{-- 1. タイトルエリア --}}
    <div class="report-header" style="margin-bottom: 30px;">
        <h2 style="font-size: 1.8rem; font-weight: bold; margin: 0 0 8px 0; border-left: 4px solid #000; padding-left: 12px;">マイ勤怠レポート</h2>
        <p style="color: #666; margin: 0; font-size: 0.95rem;">過去６ヶ月の勤怠データから集計しています。</p>
    </div>

    {{-- 2. 基本サマリー --}}
    <section class="report-section" style="margin-bottom: 40px;">
        <h3 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 15px; border-bottom: 1px solid #ddd; padding-bottom: 8px;">基本サマリー</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">

            {{-- 総労働時間 --}}
            <div style="background: #fdfdfd; border: 1px solid #eee; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); text-align: center;">
                <div style="color: #666; font-size: 0.9rem; margin-bottom: 10px; font-weight: bold;">総労働時間</div>
                <div style="font-size: 1.8rem; font-weight: bold;">
                    {{ $summary['total_work']['hours'] }}<span style="font-size: 1rem; font-weight: normal; margin-left: 2px;">時間</span>
                    {{ $summary['total_work']['minutes'] }}<span style="font-size: 1rem; font-weight: normal; margin-left: 2px;">分</span>
                </div>
            </div>

            {{-- 総残業時間 --}}
            <div style="background: #fdfdfd; border: 1px solid #eee; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); text-align: center;">
                <div style="color: #666; font-size: 0.9rem; margin-bottom: 10px; font-weight: bold;">総残業時間</div>
                <div style="font-size: 1.8rem; font-weight: bold; color: #e03131;">
                    {{ $summary['total_over']['hours'] }}<span style="font-size: 1rem; font-weight: normal; margin-left: 2px; color: #333;">時間</span>
                    {{ $summary['total_over']['minutes'] }}<span style="font-size: 1rem; font-weight: normal; margin-left: 2px; color: #333;">分</span>
                </div>
            </div>

            {{-- 平均労働時間/日 --}}
            <div style="background: #fdfdfd; border: 1px solid #eee; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); text-align: center;">
                <div style="color: #666; font-size: 0.9rem; margin-bottom: 10px; font-weight: bold;">平均労働時間/日</div>
                <div style="font-size: 1.8rem; font-weight: bold;">
                    {{ $summary['avg_work']['hours'] }}<span style="font-size: 1rem; font-weight: normal; margin-left: 2px;">時間</span>
                    {{ $summary['avg_work']['minutes'] }}<span style="font-size: 1rem; font-weight: normal; margin-left: 2px;">分</span>
                </div>
            </div>

        </div>
    </section>

    {{-- 3. 月次推移 --}}
    <section class="report-section" style="margin-bottom: 40px;">
        <h3 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 15px; border-bottom: 1px solid #ddd; padding-bottom: 8px;">月次推移(過去６ヶ月)</h3>
        <div style="background: #fff; border: 1px solid #eee; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background-color: #f8f9fa; border-bottom: 1px solid #eee;">
                        <th style="padding: 15px; font-weight: bold;">月</th>
                        <th style="padding: 15px; font-weight: bold;">労働時間</th>
                        <th style="padding: 15px; font-weight: bold;">残業時間</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($monthlyTrends as $trend)
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 15px; font-weight: bold;">{{ $trend['month'] }}</td>
                        <td style="padding: 15px;">
                            {{ $trend['work_time']['hours'] }}時間 {{ $trend['work_time']['minutes'] }}分
                        </td>
                        @if ($trend['over_time']['hours'] > 0 || $trend['over_time']['minutes'] > 0)
                        {{-- 残業がある場合は赤字・太字 --}}
                        <td style="padding: 15px; color: #e03131; font-weight: bold;">
                            {{ $trend['over_time']['hours'] }}時間 {{ $trend['over_time']['minutes'] }}分
                        </td>
                        @else
                        {{-- 残業がない場合は通常色 --}}
                        <td style="padding: 15px; color: #333; font-weight: normal;">
                            {{ $trend['over_time']['hours'] }}時間 {{ $trend['over_time']['minutes'] }}分
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    {{-- 4. 今月の異常検知 --}}
    <section class="report-section" style="margin-bottom: 20px;">
        <div style="margin-bottom: 15px; border-bottom: 1px solid #ddd; padding-bottom: 8px; display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <h3 style="font-size: 1.25rem; font-weight: bold; margin: 0;">今月の異常検知</h3>
            <span style="color: #868e96; font-size: 0.85rem;">基準：始業09:00/終業18:00/長時間労働は１日10時間超</span>
        </div>

        {{-- 💡 横並びを保証するコンテナ --}}
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; width: 100%;">

            {{-- 遅刻回数 --}}
            <div style="background: #fff; border: 1px solid #eee; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); text-align: center; border-top: 3px solid #ff922b;">
                <div style="color: #666; font-size: 0.9rem; margin-bottom: 10px; font-weight: bold;">遅刻回数</div>
                @if ($abnormalities['late'] > 0)
                <div style="font-size: 2.2rem; font-weight: bold; color: #ff922b;">
                    @else
                    <div style="font-size: 2.2rem; font-weight: bold; color: #333;">
                        @endif
                        {{ $abnormalities['late'] }}<span style="font-size: 1rem; font-weight: normal; margin-left: 4px; color: #333;">回</span>
                    </div>
                </div>

                {{-- 早退回数 --}}
                <div style="background: #fff; border: 1px solid #eee; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); text-align: center; border-top: 3px solid #fab005;">
                    <div style="color: #666; font-size: 0.9rem; margin-bottom: 10px; font-weight: bold;">早退回数</div>
                    @if ($abnormalities['early'] > 0)
                    <div style="font-size: 2.2rem; font-weight: bold; color: #fab005;">
                        @else
                        <div style="font-size: 2.2rem; font-weight: bold; color: #333;">
                            @endif
                            {{ $abnormalities['early'] }}<span style="font-size: 1rem; font-weight: normal; margin-left: 4px; color: #333;">回</span>
                        </div>
                    </div>

                    {{-- 長時間労働日数 --}}
                    <div style="background: #fff; border: 1px solid #eee; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); text-align: center; border-top: 3px solid #fa5252;">
                        <div style="color: #666; font-size: 0.9rem; margin-bottom: 10px; font-weight: bold;">長時間労働日数</div>
                        @if ($abnormalities['long_work'] > 0)
                        <div style="font-size: 2.2rem; font-weight: bold; color: #fa5252;">
                            @else
                            <div style="font-size: 2.2rem; font-weight: bold; color: #333;">
                                @endif
                                {{ $abnormalities['long_work'] }}<span style="font-size: 1rem; font-weight: normal; margin-left: 4px; color: #333;">日</span>
                            </div>
                        </div>

                    </div>
    </section>
    @endsection