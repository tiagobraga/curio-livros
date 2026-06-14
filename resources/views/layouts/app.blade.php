<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Painel') — Curió Livros</title>
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
<body class="h-full flex flex-col md:flex-row bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-blue-950/20 via-slate-950 to-slate-950 relative overflow-hidden">
    <!-- Ambient decorative blobs -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-600/5 rounded-full filter blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-amber-500/3 rounded-full filter blur-3xl pointer-events-none"></div>

    <!-- SIDEBAR (DESKTOP) -->
    <aside class="hidden md:flex md:flex-col md:w-64 bg-slate-900/40 backdrop-blur-md border-r border-slate-800/80 z-20 flex-shrink-0">
        <!-- Brand / Header -->
        <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-800/60">
            <div class="w-10 h-10 rounded-xl overflow-hidden border border-slate-700 bg-slate-950 flex-shrink-0">
                <img src="/images/curiologo.jpeg" alt="Logo Curió" class="w-full h-full object-cover">
            </div>
            <div>
                <span class="font-bold text-sm tracking-wide bg-gradient-to-r from-blue-400 to-slate-100 bg-clip-text text-transparent">Curió Livros</span>
                <span class="block text-[10px] text-slate-500 font-medium">BIBFRAME CATALOG</span>
            </div>
        </div>

        <!-- Library Context info -->
        @if(Auth::user()->library)
            <div class="px-6 py-3 bg-slate-950/30 border-b border-slate-800/30">
                <span class="block text-[10px] uppercase tracking-wider text-slate-500 font-bold">Sua Biblioteca</span>
                <span class="text-xs text-blue-400 font-medium truncate block">{{ Auth::user()->library->name }}</span>
            </div>
        @else
            <div class="px-6 py-3 bg-slate-950/30 border-b border-slate-800/30">
                <span class="block text-[10px] uppercase tracking-wider text-slate-500 font-bold">Acesso Global</span>
                <span class="text-xs text-amber-400 font-medium truncate block">Curadoria Central</span>
            </div>
        @endif

        <!-- Menu Navigation -->
        <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
            <!-- Dashboard Link -->
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 {{ Route::currentRouteName() === 'dashboard' ? 'bg-blue-600/15 text-blue-400 border-l-2 border-blue-500' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-100' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                Dashboard
            </a>

            <!-- Catalog Link -->
            <a href="{{ route('catalog.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 {{ str_starts_with(Route::currentRouteName(), 'catalog') ? 'bg-blue-600/15 text-blue-400 border-l-2 border-blue-500' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-100' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
                Catálogo Central
            </a>

            <!-- Circulation / Loans Link (Attendant, Manager, Admin) -->
            @if(Auth::user()->isAttendant())
                <a href="{{ route('loans.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 {{ str_starts_with(Route::currentRouteName(), 'loans') ? 'bg-blue-600/15 text-blue-400 border-l-2 border-blue-500' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-100' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0-4.5 4.5M21 7.5H7.5" />
                    </svg>
                    Circulação
                </a>
            @endif

            <!-- Users Link (Manager, Admin) -->
            @if(Auth::user()->isManager())
                <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 {{ str_starts_with(Route::currentRouteName(), 'users') ? 'bg-blue-600/15 text-blue-400 border-l-2 border-blue-500' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-100' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.109A2.25 2.25 0 0 1 12.75 21.5h-1.5a2.25 2.25 0 0 1-2.25-2.263V19.13m4.786-3.07M9.786 16.06a8.96 8.96 0 0 0-4.786-3.07M9.786 16.06A8.961 8.961 0 0 1 15 13c1.381 0 2.677.312 3.834.869m-8.154 2.191A8.961 8.961 0 0 0 5 13M5 13a9.06 9.06 0 0 0-2.625.372 9.337 9.337 0 0 0-4.121-.952 4.125 4.125 0 0 0 7.533-2.493M5 13v.109a2.25 2.25 0 0 0 2.25 2.263h1.5a2.25 2.25 0 0 0 2.25-2.263v-.109m0 0V14.1" />
                    </svg>
                    Usuários
                </a>
            @endif
        </nav>

        <!-- User profile footer -->
        <div class="p-4 border-t border-slate-800/60 flex items-center justify-between gap-3 bg-slate-900/20">
            <div class="truncate">
                <span class="block text-sm font-medium text-slate-200 truncate">{{ Auth::user()->name }}</span>
                <span class="block text-[10px] text-slate-500 uppercase font-bold tracking-wider">{{ Auth::user()->role }}</span>
            </div>
            <!-- Logout Button -->
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="p-2 text-slate-500 hover:text-red-400 rounded-lg hover:bg-slate-800/50 transition duration-150" title="Sair do Sistema">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M19 12H9m10 0-4-4m4 4-4 4" />
                    </svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- MOBILE TOP BAR -->
    <header class="flex md:hidden items-center justify-between px-4 py-3 bg-slate-900/60 backdrop-blur-md border-b border-slate-800/80 z-20 flex-shrink-0">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg overflow-hidden border border-slate-700 bg-slate-950">
                <img src="/images/curiologo.jpeg" alt="Logo Curió" class="w-full h-full object-cover">
            </div>
            <span class="font-bold text-sm tracking-wide text-slate-100">Curió Livros</span>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs text-blue-400 font-medium max-w-[120px] truncate">
                {{ Auth::user()->library ? Auth::user()->library->name : 'Curadoria' }}
            </span>
            <!-- Mobile Logout -->
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="p-1 text-slate-400 hover:text-red-400 transition" title="Sair">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M19 12H9m10 0-4-4m4 4-4 4" />
                    </svg>
                </button>
            </form>
        </div>
    </header>

    <!-- MAIN APP CONTENT VIEW -->
    <main class="flex-1 flex flex-col min-h-0 overflow-y-auto z-10 pb-20 md:pb-0">
        <div class="flex-1 p-4 md:p-8 max-w-7xl w-full mx-auto">
            <!-- Session Messages -->
            @if(session('success'))
                <div class="mb-6 bg-emerald-950/40 border border-emerald-800/80 rounded-2xl p-4 flex gap-3 items-center text-emerald-400 text-sm shadow-lg shadow-emerald-950/20">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-emerald-500 flex-shrink-0">
                        <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.74-5.24Z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-950/40 border border-red-800/80 rounded-2xl p-4 flex gap-3 items-center text-red-400 text-sm shadow-lg shadow-red-950/20">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-red-500 flex-shrink-0">
                        <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Content Slot -->
            @yield('content')
        </div>
    </main>

    <!-- MOBILE BOTTOM TAB NAVIGATION -->
    <nav class="flex md:hidden fixed bottom-0 left-0 right-0 h-16 bg-slate-900/90 backdrop-blur-xl border-t border-slate-800/80 z-20 items-center justify-around px-2">
        <!-- Dashboard link -->
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center flex-1 py-1 text-xs font-medium transition duration-150 {{ Route::currentRouteName() === 'dashboard' ? 'text-blue-400' : 'text-slate-500 hover:text-slate-300' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mb-1">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
            Início
        </a>

        <!-- Catalog link -->
        <a href="{{ route('catalog.index') }}" class="flex flex-col items-center justify-center flex-1 py-1 text-xs font-medium transition duration-150 {{ str_starts_with(Route::currentRouteName(), 'catalog') ? 'text-blue-400' : 'text-slate-500 hover:text-slate-300' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mb-1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
            </svg>
            Catálogo
        </a>

        <!-- Scanner link (Attendant, Manager, Admin) -->
        @if(Auth::user()->isAttendant())
            <a href="{{ route('catalog.create') }}" class="flex flex-col items-center justify-center flex-1 py-1 text-xs font-medium transition duration-150 {{ Route::currentRouteName() === 'catalog.create' ? 'text-blue-400' : 'text-slate-500 hover:text-slate-300' }}">
                <div class="relative flex items-center justify-center w-10 h-10 -mt-5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white rounded-full shadow-lg shadow-blue-500/30 border-2 border-slate-950 transition duration-150 active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 47.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                    </svg>
                </div>
                <span class="block mt-1 text-[10px]">Escanear</span>
            </a>
        @endif

        <!-- Loans Link (Attendant, Manager, Admin) -->
        @if(Auth::user()->isAttendant())
            <a href="{{ route('loans.index') }}" class="flex flex-col items-center justify-center flex-1 py-1 text-xs font-medium transition duration-150 {{ str_starts_with(Route::currentRouteName(), 'loans') ? 'text-blue-400' : 'text-slate-500 hover:text-slate-300' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mb-1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0-4.5 4.5M21 7.5H7.5" />
                </svg>
                Empréstimos
            </a>
        @endif
    </nav>

    @yield('scripts')
</body>
</html>
