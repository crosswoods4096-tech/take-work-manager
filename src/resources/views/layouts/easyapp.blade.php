<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH 勤怠管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- CSS 読み込み --}}
    @yield('css')
</head>

<body>

    {{-- ヘッダー --}}
    @include('components.easyheader')

    {{-- コンテンツ --}}
    @yield('content')

</body>

</html>