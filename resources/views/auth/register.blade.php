<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-brand-creme text-brand-text">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Criar Conta de Leitor — Biblió') }}</title>
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
<body class="h-full flex items-center justify-center p-6 bg-brand-creme relative overflow-x-hidden">

    <!-- Language selector dropdown at top right -->
    <div style="position: absolute; top: 20px; right: 24px; z-index: 20;">
        <select id="language-select-register" onchange="window.location.href='/language/' + this.value"
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
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-36 h-16 rounded-2xl overflow-hidden mb-4 border border-brand-border shadow-md bg-white p-2">
                    <img src="/images/curiologo.png" alt="Logo Curió" class="h-full object-contain">
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-primary">{{ __('Cadastro de Leitor') }}</h1>
                <p class="text-sm text-slate-500 mt-1">{{ __('Crie sua conta para solicitar empréstimos') }}</p>
            </div>

            <!-- Register Form -->
            <form action="{{ route('register.post') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Name field -->
                <div class="space-y-1">
                    <label for="name" class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Nome Completo') }}</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-400 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition duration-200"
                        placeholder="{{ __('Seu nome completo') }}">
                    @error('name')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email field -->
                <div class="space-y-1">
                    <label for="email" class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('E-mail') }}</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-400 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition duration-200"
                        placeholder="seuemail@provedor.com">
                    @error('email')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone field -->
                <div class="space-y-1">
                    @include('partials.phone-input')
                </div>

                <!-- Library choice -->
                <div class="space-y-1">
                    <label for="library_id" class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Unidade Preferencial') }}</label>
                    <select name="library_id" id="library_id" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition duration-200">
                        <option value="" disabled selected>{{ __('Selecione sua unidade...') }}</option>
                        @foreach($libraries as $lib)
                            <option value="{{ $lib->id }}" {{ old('library_id') == $lib->id ? 'selected' : '' }}>
                                {{ $lib->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('library_id')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password field -->
                <div class="space-y-1">
                    <label for="password" class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Senha (mín. 8 caracteres)') }}</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                            class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-4 pr-10 py-2.5 text-sm text-slate-100 placeholder-slate-400 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition duration-200"
                            placeholder="••••••••">
                        <button type="button" onclick="togglePasswordVisibility('password', this)" class="absolute right-3 top-3 text-slate-400 hover:text-slate-200 focus:outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 eye-icon">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 hidden eye-slash-icon">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Confirmation field -->
                <div class="space-y-1">
                    <label for="password_confirmation" class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Confirmar Senha') }}</label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                            class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-4 pr-10 py-2.5 text-sm text-slate-100 placeholder-slate-400 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition duration-200"
                            placeholder="••••••••">
                        <button type="button" onclick="togglePasswordVisibility('password_confirmation', this)" class="absolute right-3 top-3 text-slate-400 hover:text-slate-200 focus:outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 eye-icon">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 hidden eye-slash-icon">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Submit button -->
                <button type="submit"
                    class="w-full bg-primary hover:bg-primary-hover text-white font-semibold rounded-xl py-3 text-sm transition duration-200 shadow-md active:scale-[0.98] mt-2">
                    {{ __('Registrar Conta') }}
                </button>
            </form>

            <!-- Back to Login Link -->
            <div class="mt-6 text-center text-sm border-t border-brand-border/40 pt-5">
                <span class="text-slate-500">{{ __('Já possui uma conta?') }}</span>
                <a href="{{ route('login') }}" class="font-semibold text-primary hover:text-primary-hover ml-1 transition">
                    {{ __('Fazer Login →') }}
                </a>
            </div>
        </div>

        <!-- Footer link -->
        <p class="text-center text-xs text-slate-500 mt-6">
            &copy; 2026 {{ __('Grupo de Pesquisas Curió. Todos os direitos reservados.') }}
        </p>
    </div>

    <script>
        function togglePasswordVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            const eyeIcon = btn.querySelector('.eye-icon');
            const eyeSlashIcon = btn.querySelector('.eye-slash-icon');
            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeSlashIcon.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeSlashIcon.classList.add('hidden');
            }
        }
    </script>
</body>
</html>

