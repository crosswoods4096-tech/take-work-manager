@extends('layouts.app')

@section('content')
<div class="admin-staff-container" style="padding: 20px; max-width: 1000px; margin: 0 auto;">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0; font-size: 1.5rem; font-weight: bold;">スタッフ一覧</h2>
        <a href="{{ route('admin.attendance.daily') }}" style="text-decoration: none; color: #007bff; font-size: 0.9em;">← 日次勤怠一覧に戻る</a>
    </div>

    <div class="table-wrapper" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background-color: #f5f5f5; border-bottom: 1px solid #ddd;">
                    <th style="padding: 12px 15px; width: 100px;">スタッフID</th>
                    <th style="padding: 12px 15px;">名前</th>
                    <th style="padding: 12px 15px;">メールアドレス</th>
                    <th style="padding: 12px 15px; text-align: center; width: 150px;">勤怠確認</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($staffs as $staff)
                <tr style="border-bottom: 1px solid #eee;">
                    {{-- スタッフID --}}
                    <td style="padding: 12px 15px; color: #777;">#{{ $staff->id }}</td>

                    {{-- 名前 --}}
                    <td style="padding: 12px 15px; font-weight: bold; color: #333;">{{ $staff->name }}</td>

                    {{-- メールアドレス --}}
                    <td style="padding: 12px 15px; color: #555;">{{ $staff->email }}</td>

                    {{-- 月次勤怠へのリンク --}}
                    <td style="padding: 12px 15px; text-align: center;">
                        <a href="{{ route('admin.staff.attendance', ['id' => $staff->id]) }}" style="background: #333; color: #fff; text-decoration: none; padding: 6px 12px; border-radius: 4px; font-size: 0.85em; font-weight: bold; display: inline-block;">
                            詳細
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="padding: 30px; text-align: center; color: #999;">
                        登録されている一般スタッフはいません。
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection