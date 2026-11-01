@php
    $user = Auth::user();
    $activeLibrary = $user && $user->library ? $user->library : (isset($library) ? $library : null);
    $colorTheme = $activeLibrary ? $activeLibrary->getColorTheme() : \App\Models\Library::getColorSchemes()['emerald'];
    $customLogo = ($activeLibrary && $activeLibrary->custom_logo_url) ? $activeLibrary->custom_logo_url : '/images/bibliologo.jpg';
    $customSubtitle = ($activeLibrary && $activeLibrary->subtitle) ? $activeLibrary->subtitle : __('Biblioteca Inteligente');
    $currentLocale = App::getLocale();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $currentLocale) }}" class="h-full bg-brand-creme text-brand-text">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('Início')) — Biblió: {{ $customSubtitle }}</title>
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
        :root {
            --color-primary: {{ $colorTheme['primary'] }};
            --color-primary-hover: {{ $colorTheme['primary_hover'] }};
        }
        .bg-primary { background-color: {{ $colorTheme['primary'] }} !important; }
        .hover\:bg-primary-hover:hover { background-color: {{ $colorTheme['primary_hover'] }} !important; }
        .text-primary { color: {{ $colorTheme['primary'] }} !important; }
        .border-primary { border-color: {{ $colorTheme['primary'] }} !important; }
        .border-primary-hover { border-color: {{ $colorTheme['border'] }} !important; }
        .text-emerald-200 { color: {{ $colorTheme['light_text'] }} !important; }
    </style>
    @yield('head')
</head>
<body class="h-full flex flex-col md:flex-row bg-brand-creme text-brand-text relative overflow-x-hidden">

    <!-- SIDEBAR (DESKTOP) -->
    <aside class="hidden md:flex md:flex-col md:w-64 bg-primary text-emerald-50 border-r border-primary-hover z-20 flex-shrink-0 shadow-xl">
        <!-- Brand / Header -->
        <div class="flex flex-col items-center justify-center px-4 py-6 border-b border-primary-hover text-center">
            <img src="{{ $customLogo }}" alt="Logo {{ $activeLibrary->name ?? 'Biblió' }}" class="w-36 max-w-full h-auto object-contain rounded-xl shadow-sm mb-3">
            <span class="font-bold text-base tracking-wide text-white block leading-tight">{{ $customSubtitle }}</span>
        </div>

        <!-- Library Context info -->
        @if(Auth::user()->libraries->count() > 0)
            <div class="px-6 py-3.5 bg-black/15 border-b border-primary-hover">
                <span class="block text-[10px] uppercase tracking-wider text-emerald-200 font-bold mb-1.5">
                    {{ Auth::user()->libraries->count() > 1 ? __('Suas Bibliotecas') : __('Sua Biblioteca') }}
                </span>
                <div class="space-y-1.5">
                    @foreach(Auth::user()->libraries as $lib)
                        <div class="flex items-center justify-between gap-2 text-xs text-white font-medium truncate">
                            <div class="flex items-center gap-2 truncate min-w-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-200 flex-shrink-0"></span>
                                <span class="truncate">{{ $lib->name }}</span>
                            </div>
                            @if(Auth::user()->isCurator() || Auth::user()->isManagerOf($lib->id))
                                <a href="{{ route('libraries.edit', $lib->id) }}" class="text-[10px] text-emerald-200/80 hover:text-white hover:underline flex-shrink-0">({{ __('editar') }})</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="px-6 py-3.5 bg-black/15 border-b border-primary-hover">
                <span class="block text-[10px] uppercase tracking-wider text-emerald-200 font-bold mb-1">{{ __('Acesso Global') }}</span>
                <span class="text-xs text-amber-300 font-semibold truncate block">{{ __('Curadoria Central') }}</span>
            </div>
        @endif

        <!-- Menu Navigation -->
        <nav class="flex-1 px-4 py-4 space-y-1.5 overflow-y-auto">
            <!-- Dashboard Link -->
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 {{ Route::currentRouteName() === 'dashboard' ? 'bg-primary-hover text-white font-semibold shadow-inner border-l-4 border-emerald-200' : 'text-emerald-100/90 hover:bg-primary-hover/60 hover:text-white' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                {{ __('Início') }}
            </a>

            <!-- Catalog Link -->
            <a href="{{ route('catalog.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 {{ str_starts_with(Route::currentRouteName() ?? '', 'catalog') && Route::currentRouteName() !== 'catalog.suggested_edits' ? 'bg-primary-hover text-white font-semibold shadow-inner border-l-4 border-emerald-200' : 'text-emerald-100/90 hover:bg-primary-hover/60 hover:text-white' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
                {{ __('Catálogo') }}
            </a>

            <!-- Circulation / Loans Link (Attendant, Manager, Admin) -->
            @if(Auth::user()->isAttendant())
                <a href="{{ route('loans.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 {{ str_starts_with(Route::currentRouteName() ?? '', 'loans') ? 'bg-primary-hover text-white font-semibold shadow-inner border-l-4 border-emerald-200' : 'text-emerald-100/90 hover:bg-primary-hover/60 hover:text-white' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0-4.5 4.5M21 7.5H7.5" />
                    </svg>
                    {{ __('Empréstimos') }}
                </a>
            @endif

            <!-- Moderation (Curator, Admin) -->
            @if(Auth::user()->isCurator())
                <a href="{{ route('catalog.suggested_edits') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 {{ Route::currentRouteName() === 'catalog.suggested_edits' ? 'bg-primary-hover text-white font-semibold shadow-inner border-l-4 border-emerald-200' : 'text-emerald-100/90 hover:bg-primary-hover/60 hover:text-white' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    {{ __('Moderação') }}
                </a>
            @endif

            <!-- Users Link (Attendant, Manager, Curator, Admin) - Clean modern SVG -->
            @if(Auth::user()->isAttendant() || Auth::user()->isCurator())
                <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 {{ str_starts_with(Route::currentRouteName() ?? '', 'users') ? 'bg-primary-hover text-white font-semibold shadow-inner border-l-4 border-emerald-200' : 'text-emerald-100/90 hover:bg-primary-hover/60 hover:text-white' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                    {{ __('Usuários') }}
                </a>
            @endif

            <!-- Libraries Link (Curator, Admin) -->
            @if(Auth::user()->isCurator())
                <a href="{{ route('libraries.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 {{ str_starts_with(Route::currentRouteName() ?? '', 'libraries') ? 'bg-primary-hover text-white font-semibold shadow-inner border-l-4 border-emerald-200' : 'text-emerald-100/90 hover:bg-primary-hover/60 hover:text-white' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.5M4.5 21V10.5" />
                    </svg>
                    {{ __('Bibliotecas') }}
                </a>
            @endif
        </nav>

        <!-- Language Selector Dropdown -->
        <div class="px-4 py-3 border-t border-primary-hover bg-black/20 text-center">
            <div class="flex items-center justify-center gap-2 text-xs text-emerald-100/80">
                <label for="language-select-desktop" class="text-xs font-bold text-emerald-200/90 whitespace-nowrap">{{ __('Idioma') }}:</label>
                <select id="language-select-desktop" onchange="window.location.href='/language/' + this.value"
                    class="bg-black/40 text-white text-xs border border-white/20 rounded-lg px-2 py-1 focus:outline-none focus:border-emerald-300 cursor-pointer">
                    <option value="pt_BR" class="bg-slate-900 text-white" {{ $currentLocale === 'pt_BR' ? 'selected' : '' }}>Português (pt-BR)</option>
                    <option value="en_US" class="bg-slate-900 text-white" {{ $currentLocale === 'en_US' ? 'selected' : '' }}>English (en-US)</option>
                    <option value="es" class="bg-slate-900 text-white" {{ $currentLocale === 'es' ? 'selected' : '' }}>Español (es)</option>
                </select>
            </div>
        </div>

        <!-- User profile footer -->
        <div class="p-4 border-t border-primary-hover flex items-center justify-between gap-3 bg-black/30">
            <div class="truncate">
                <a href="{{ route('profile.edit') }}" class="block text-sm font-semibold text-white hover:underline truncate" title="{{ __('Editar perfil') }}">{{ Auth::user()->name }}</a>
                <a href="{{ route('profile.edit') }}" class="block text-xs font-normal text-emerald-200/80 hover:text-white hover:underline">{{ __('Editar perfil') }}</a>
            </div>
            <!-- Logout Button -->
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="p-2 text-emerald-200 hover:text-red-300 rounded-lg hover:bg-black/40 transition duration-150" title="{{ __('Sair do Sistema') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M19 12H9m10 0-4-4m4 4-4 4" />
                    </svg>
                </button>
            </form>
        </div>

        <!-- Open Source Attribution Footer (LAST ELEMENT IN DESKTOP SIDEBAR) -->
        <div class="px-4 py-2.5 bg-black/50 border-t border-primary-hover text-center text-[10px] text-emerald-200/70 leading-tight">
            {{ __('Uma iniciativa open source do grupo de pesquisa') }}
            <a href="https://curio.ia.br" target="_blank" rel="noopener noreferrer" class="hover:text-white font-semibold underline underline-offset-2">Curió</a>
            · CC-BY
        </div>
    </aside>

    <!-- MOBILE TOP BAR -->
    <header class="flex md:hidden items-center justify-between px-3 py-2 bg-primary text-white border-b border-primary-hover z-20 flex-shrink-0 shadow-md">
        <div class="flex items-center gap-2 min-w-0">
            <img src="{{ $customLogo }}" alt="Logo {{ $activeLibrary->name ?? 'Biblió' }}" style="height: 36px; width: auto;" class="h-9 w-auto object-contain rounded-lg bg-white p-1 shadow-sm flex-shrink-0">
            <span class="font-bold text-xs tracking-wide text-white truncate">{{ $customSubtitle }}</span>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0 ml-2">
            <!-- Mobile Language Selector Dropdown -->
            <select id="language-select-mobile" onchange="window.location.href='/language/' + this.value"
                class="bg-black/40 text-white text-[11px] border border-white/20 rounded-md px-1.5 py-0.5 focus:outline-none">
                <option value="pt_BR" class="bg-slate-900 text-white" {{ $currentLocale === 'pt_BR' ? 'selected' : '' }}>PT</option>
                <option value="en_US" class="bg-slate-900 text-white" {{ $currentLocale === 'en_US' ? 'selected' : '' }}>EN</option>
                <option value="es" class="bg-slate-900 text-white" {{ $currentLocale === 'es' ? 'selected' : '' }}>ES</option>
            </select>
            <!-- Mobile Logout -->
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="p-1.5 text-emerald-200 hover:text-red-300 transition" title="{{ __('Sair do Sistema') }}">
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
                <div class="mb-6 bg-emerald-100 border border-emerald-300 rounded-2xl p-4 flex gap-3 items-center text-emerald-900 text-sm shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-emerald-700 flex-shrink-0">
                        <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.74-5.24Z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-100 border border-red-300 rounded-2xl p-4 flex gap-3 items-center text-red-900 text-sm shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-red-700 flex-shrink-0">
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
    <nav class="flex md:hidden fixed bottom-0 left-0 right-0 h-16 bg-white/95 backdrop-blur-xl border-t border-brand-border/80 z-30 items-center justify-around px-1 shadow-lg">
        <!-- Dashboard link -->
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center flex-1 py-1 text-[10px] font-medium transition duration-150 {{ Route::currentRouteName() === 'dashboard' ? 'text-primary font-bold' : 'text-slate-500 hover:text-slate-800' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mb-0.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
            <span>{{ __('Início') }}</span>
        </a>

        <!-- Catalog link -->
        <a href="{{ route('catalog.index') }}" class="flex flex-col items-center justify-center flex-1 py-1 text-[10px] font-medium transition duration-150 {{ str_starts_with(Route::currentRouteName() ?? '', 'catalog') && Route::currentRouteName() !== 'catalog.suggested_edits' ? 'text-primary font-bold' : 'text-slate-500 hover:text-slate-800' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mb-0.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
            </svg>
            <span>{{ __('Catálogo') }}</span>
        </a>

        <!-- Scanner Link (Attendant, Manager, Curator, Admin) -->
        @if(Auth::user()->isAttendant())
            <a href="{{ route('catalog.create') }}" class="flex flex-col items-center justify-center flex-1 py-1 text-[10px] font-medium transition duration-150 {{ Route::currentRouteName() === 'catalog.create' ? 'text-primary font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mb-0.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 47.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                </svg>
                <span>{{ __('Escanear') }}</span>
            </a>

            <!-- Loans Link -->
            <a href="{{ route('loans.index') }}" class="flex flex-col items-center justify-center flex-1 py-1 text-[10px] font-medium transition duration-150 {{ str_starts_with(Route::currentRouteName() ?? '', 'loans') ? 'text-primary font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mb-0.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0-4.5 4.5M21 7.5H7.5" />
                </svg>
                <span>{{ __('Empréstimos') }}</span>
            </a>
        @endif

        <!-- Users Link (Attendants, Managers, Curators, Admins) -->
        @if(Auth::user()->isAttendant() || Auth::user()->isCurator())
            <a href="{{ route('users.index') }}" class="flex flex-col items-center justify-center flex-1 py-1 text-[10px] font-medium transition duration-150 {{ str_starts_with(Route::currentRouteName() ?? '', 'users') ? 'text-primary font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mb-0.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                </svg>
                <span>{{ __('Usuários') }}</span>
            </a>
        @endif

        <!-- Profile Link (Always available on mobile for all users) -->
        <a href="{{ route('profile.edit') }}" class="flex flex-col items-center justify-center flex-1 py-1 text-[10px] font-medium transition duration-150 {{ Route::currentRouteName() === 'profile.edit' ? 'text-primary font-bold' : 'text-slate-500 hover:text-slate-800' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mb-0.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
            <span>{{ __('Perfil') }}</span>
        </a>
    </nav>

    @yield('scripts')
</body>
</html>
