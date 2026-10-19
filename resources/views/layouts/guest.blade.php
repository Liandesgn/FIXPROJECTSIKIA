<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#FCE7F0">
    <title>@yield('title', 'Masuk') — SI-KIA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="m-0 min-h-screen bg-[#FCE7F0] font-sans antialiased">
    @yield('content')
</body>
</html>