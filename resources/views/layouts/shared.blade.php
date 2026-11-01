<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-brand-creme text-brand-text">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Biblió')</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind and Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
    </style>
    @yield('head')
</head>
<body class="min-h-full flex flex-col bg-brand-creme text-brand-text relative overflow-x-hidden">
    <!-- Top Green Bar (same primary green color) -->
    <header class="bg-primary text-emerald-50 py-3.5 shadow-md z-20">
        <div class="max-w-4xl w-full mx-auto px-6 md:px-8 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-16 h-8 rounded-md overflow-hidden border border-emerald-700 bg-white flex-shrink-0 p-1 flex items-center justify-center">
                    <img src="/images/curiologo.png" alt="Logo Curió" class="h-full object-contain">
                </div>
                <span class="font-bold text-sm tracking-wide text-white">Biblió</span>
            </div>
            <div>
                <a href="/" class="text-xs font-semibold text-emerald-200 hover:text-white transition duration-150">
                    Entre na comunidade Biblió!
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="flex-1 max-w-4xl w-full mx-auto p-6 md:p-8">
        @yield('content')
    </main>

    @yield('scripts')
</body>
</html>
