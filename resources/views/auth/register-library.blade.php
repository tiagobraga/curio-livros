<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-brand-creme text-brand-text">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Criar Biblioteca — Biblió') }}</title>
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
        <select id="language-select-register-lib" onchange="window.location.href='/language/' + this.value"
            style="background: #ffffff; color: #1E4D2B; font-size: 0.78rem; font-weight: 600; border: 1px solid #ddd9d0; border-radius: 10px; padding: 6px 12px; cursor: pointer; outline: none; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
            <option value="pt_BR" {{ app()->getLocale() === 'pt_BR' ? 'selected' : '' }}>Português (BR)</option>
            <option value="en_US" {{ app()->getLocale() === 'en_US' ? 'selected' : '' }}>English (US)</option>
            <option value="es" {{ app()->getLocale() === 'es' ? 'selected' : '' }}>Español</option>
        </select>
    </div>

    <div class="w-full max-w-2xl z-10 my-8">
        <!-- Card Container -->
        <div class="bg-white border border-brand-border rounded-3xl p-8 shadow-xl">
            <!-- Header/Logo -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center h-16 rounded-2xl overflow-hidden mb-3 border border-brand-border shadow-sm bg-white p-2">
                    <img src="/images/bibliologo.jpg" alt="Logo Biblió" class="h-full object-contain">
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-primary">{{ __('Crie sua Biblioteca') }}</h1>
                <p class="text-sm text-slate-500 mt-1">{{ __('Cadastre sua conta de gestor e configure sua biblioteca em uma única etapa.') }}</p>
            </div>

            <!-- Combined Form -->
            <form action="{{ route('register-library.post') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Section 1: Manager Account Info -->
                <div class="space-y-4">
                    <h3 class="text-xs uppercase font-bold text-slate-400 tracking-wider border-b border-brand-border/60 pb-1.5">{{ __('Dados do Gestor (Conta)') }}</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- User Name -->
                        <div class="space-y-1.5">
                            <label for="name" class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Nome Completo') }} <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-primary transition duration-200"
                                placeholder="{{ __('Seu nome completo') }}">
                            @error('name') <p class="text-xs text-red-650 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- User Email -->
                        <div class="space-y-1.5">
                            <label for="email" class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('E-mail') }} <span class="text-red-500">*</span></label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-primary transition duration-200"
                                placeholder="seuemail@provedor.com">
                            @error('email') <p class="text-xs text-red-655 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- User Phone -->
                        <div class="space-y-1.5 sm:col-span-2">
                            @include('partials.phone-input')
                        </div>

                        <!-- User Password -->
                        <div class="space-y-1.5">
                            <label for="password" class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Senha (mín. 8 caracteres)') }} <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="password" name="password" id="password" required
                                    class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-4 pr-10 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-primary transition duration-200"
                                    placeholder="••••••••">
                                <button type="button" onclick="togglePasswordVisibility('password', this)" class="absolute right-3 top-2.5 text-slate-450 hover:text-slate-200 focus:outline-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 eye-icon">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 hidden eye-slash-icon">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                            @error('password') <p class="text-xs text-red-650 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- User Password Confirmation -->
                        <div class="space-y-1.5">
                            <label for="password_confirmation" class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Confirmar Senha') }} <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="password" name="password_confirmation" id="password_confirmation" required
                                    class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-4 pr-10 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-primary transition duration-200"
                                    placeholder="••••••••">
                                <button type="button" onclick="togglePasswordVisibility('password_confirmation', this)" class="absolute right-3 top-2.5 text-slate-455 hover:text-slate-200 focus:outline-none">
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
                    </div>
                </div>

                <!-- Section 2: Library Info -->
                <div class="space-y-4 pt-4">
                    <h3 class="text-xs uppercase font-bold text-slate-400 tracking-wider border-b border-brand-border/60 pb-1.5">{{ __('Dados da Biblioteca') }}</h3>
                    
                    <!-- Library Name / Title -->
                    <div class="space-y-1.5">
                        <label for="library_name" class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Título da biblioteca') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="library_name" id="library_name" value="{{ old('library_name') }}" required
                            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-primary transition duration-200"
                            placeholder="{{ __('ex: Minha Biblioteca Pessoal, Biblioteca Comunitária do Bairro') }}">
                        @error('library_name') <p class="text-xs text-red-650 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Description -->
                    <div class="space-y-1.5">
                        <label for="description" class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Descrição') }}</label>
                        <textarea name="description" id="description" rows="3"
                            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-primary transition duration-200"
                            placeholder="{{ __('Breve descrição da sua biblioteca, acervo ou missão.') }}">{{ old('description') }}</textarea>
                        @error('description') <p class="text-xs text-red-650 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Address -->
                    <div class="space-y-1.5">
                        <label for="address" class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Endereço') }}</label>
                        <input type="text" name="address" id="address" value="{{ old('address') }}"
                            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-primary transition duration-200"
                            placeholder="{{ __('ex: Rua Flores, 123 - Centro') }}">
                        @error('address') <p class="text-xs text-red-650 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Library Type -->
                        <div class="space-y-1.5">
                            <label for="library_type" class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Tipo de biblioteca') }}</label>
                            <select name="library_type" id="library_type"
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-primary transition duration-200">
                                <option value="Pessoal" {{ old('library_type', 'Pessoal') == 'Pessoal' ? 'selected' : '' }}>{{ __('Pessoal') }}</option>
                                <option value="Pública" {{ old('library_type') == 'Pública' ? 'selected' : '' }}>{{ __('Pública') }}</option>
                                <option value="Universitária" {{ old('library_type') == 'Universitária' ? 'selected' : '' }}>{{ __('Universitária') }}</option>
                                <option value="Especializada" {{ old('library_type') == 'Especializada' ? 'selected' : '' }}>{{ __('Especializada') }}</option>
                                <option value="Outro" {{ old('library_type') == 'Outro' ? 'selected' : '' }}>{{ __('Outro') }}</option>
                            </select>
                            @error('library_type') <p class="text-xs text-red-650 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Custom Share Code -->
                        <div class="space-y-1.5">
                            <label for="share_code" class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Código de compartilhamento') }}</label>
                            <input type="text" name="share_code" id="share_code" value="{{ old('share_code') }}"
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-primary transition duration-200"
                                placeholder="{{ __('ex: minhabib (deixe em branco para gerar automático)') }}">
                            @error('share_code') <p class="text-xs text-red-650 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Is Public Checkbox -->
                    <div class="flex items-center pt-2">
                        <input type="checkbox" name="is_public_page" id="is_public_page" value="1" {{ old('is_public_page') ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-slate-800 bg-white text-primary focus:ring-primary focus:ring-offset-white">
                        <label for="is_public_page" class="text-sm text-slate-500 ml-2 select-none">{{ __('Tornar catálogo público? (Disponibiliza um link curto público do acervo)') }}</label>
                        @error('is_public_page') <p class="text-xs text-red-650 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Submit and Back Actions -->
                <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-6 border-t border-brand-border/60">
                    <a href="{{ route('login') }}" class="w-full sm:w-auto text-center font-bold text-sm text-slate-500 hover:text-primary transition px-6 py-2.5">
                        {{ __('Cancelar') }}
                    </a>
                    <button type="submit"
                        class="w-full sm:w-auto bg-primary hover:bg-primary-hover text-white font-bold rounded-xl px-8 py-3 text-sm transition duration-200 shadow-md active:scale-[0.98]">
                        {{ __('Criar Conta e Biblioteca') }}
                    </button>
                </div>
            </form>
        </div>
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
