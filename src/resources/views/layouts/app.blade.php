<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH 勤怠管理</title>

    {{-- ① sanitize.css を最優先で読み込む --}}
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">

    {{-- ② Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- ③ 共通ヘッダーCSS --}}
    <link rel="stylesheet" href="{{ asset('css/components/header.css') }}">

    {{-- ④ 各ページ固有のCSS --}}
    @yield('css')
</head>

<body>

    {{-- ヘッダー --}}
    @include('components.header')

    {{-- コンテンツ --}}
    @yield('content')

</body>

</html>