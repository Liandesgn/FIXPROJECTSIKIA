<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Masuk') — SI-KIA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gradient-to-br from-sky-700 via-sky-600 to-emerald-500 font-sans">
    <div class="flex min-h-full items-center justify-center px-4 py-12">
        @yield('content')
    </div>
</body>
</html>
