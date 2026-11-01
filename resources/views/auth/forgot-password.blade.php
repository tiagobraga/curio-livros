<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-brand-creme text-brand-text">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Recuperar Senha — Biblió') }}</title>
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
</head>
<body class="min-h-full flex items-center justify-center p-6 bg-brand-creme relative overflow-x-hidden pt-6">

    <!-- Top Green Line -->
    <div class="h-1.5 bg-primary w-full absolute top-0 left-0 z-50"></div>

    <!-- Language selector dropdown at top right -->
    <div style="position: absolute; top: 20px; right: 24px; z-index: 50;">
        <select id="language-select-forgot-pw" onchange="window.location.href='/language/' + this.value"
            style="background: #ffffff; color: #1E4D2B; font-size: 0.78rem; font-weight: 600; border: 1px solid #ddd9d0; border-radius: 10px; padding: 6px 12px; cursor: pointer; outline: none; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
            <option value="pt_BR" {{ app()->getLocale() === 'pt_BR' ? 'selected' : '' }}>Português (BR)</option>
            <option value="en_US" {{ app()->getLocale() === 'en_US' ? 'selected' : '' }}>English (US)</option>
            <option value="es" {{ app()->getLocale() === 'es' ? 'selected' : '' }}>Español</option>
        </select>
    </div>

    <div class="w-full max-w-md z-10">
        <!-- Card Container -->
        <div class="bg-white border border-brand-border rounded-3xl p-8 shadow-xl">
            <!-- Header/Logo -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center h-16 rounded-2xl overflow-hidden mb-3 border border-brand-border shadow-sm bg-white p-2">
                    <img src="/images/bibliologo.jpg" alt="Logo Biblió" class="h-full object-contain">
                </div>
                <h1 class="text-xl font-bold tracking-tight text-primary">{{ __('Esqueceu sua senha?') }}</h1>
                <p class="text-xs text-slate-500 mt-1">{{ __('Informe seu e-mail cadastrado e enviaremos um link de recuperação.') }}</p>
            </div>

            <!-- Session Status / Errors -->
            @if (session('status'))
                <div class="mb-5 bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex gap-3 items-center text-emerald-800 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-emerald-600 flex-shrink-0">
                        <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.74-5.24Z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @error('email')
                <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4 flex gap-3 items-center text-red-800 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-red-650 flex-shrink-0">
                        <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ $message }}</span>
                </div>
            @enderror

            <!-- Form -->
            <form action="{{ route('password.email') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Email Input -->
                <div class="space-y-1.5">
                    <label for="email" class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('E-mail') }}</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-primary transition duration-200"
                        placeholder="seuemail@provedor.com">
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full bg-primary hover:bg-primary-hover text-white font-bold rounded-xl py-3.5 text-sm transition duration-200 shadow-md active:scale-[0.98]">
                    {{ __('Enviar Link de Recuperação') }}
                </button>
            </form>

            <!-- Back to Login -->
            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-xs font-semibold text-slate-500 hover:text-primary transition underline underline-offset-4">
                    {{ __('Voltar para o Login') }}
                </a>
            </div>
        </div>
    </div>
</body>
</html>
