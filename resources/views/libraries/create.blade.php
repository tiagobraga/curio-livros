@extends('layouts.app')

@section('title', __('Cadastrar Biblioteca'))

@section('content')
<div class="space-y-8">
    <!-- Back Link -->
    <div>
        <a href="javascript:history.back()" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-slate-200 transition duration-150">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            {{ __('Voltar') }}
        </a>
    </div>

    <!-- Header -->
    <div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-100">{{ __('Cadastrar Nova Biblioteca') }}</h1>
        <p class="text-sm text-slate-400 mt-1">{{ __('Insira as informações e personalize a nova biblioteca.') }}</p>
    </div>

    <!-- Form Container -->
    <div class="max-w-3xl bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 md:p-8 shadow-xl">
        <form id="library-create-form" action="{{ route('libraries.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf

            <!-- SEÇÃO 1: INFORMAÇÕES GERAIS -->
            <div class="space-y-5">
                <h2 class="text-lg font-bold text-slate-100 border-b border-slate-800 pb-3 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-primary">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.5M4.5 21V10.5" />
                    </svg>
                    {{ __('Informações Gerais') }}
                </h2>

                <!-- Título da biblioteca -->
                <div class="space-y-1.5">
                    <label for="name" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Título da biblioteca') }} <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" required value="{{ old('name') }}" placeholder="{{ __('ex: Minha Biblioteca Pessoal, Biblioteca Central') }}"
                        class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-550 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-200">
                    @error('name')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Descrição -->
                <div class="space-y-1.5">
                    <label for="description" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Descrição') }}</label>
                    <textarea id="description" name="description" rows="3" placeholder="{{ __('Breve descrição da biblioteca...') }}"
                        class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-550 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-200">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Endereço -->
                    <div class="space-y-1.5">
                        <label for="address" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Endereço') }}</label>
                        <input type="text" id="address" name="address" value="{{ old('address') }}" placeholder="{{ __('ex: Av. Paulista, 1000 - São Paulo') }}"
                            class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-550 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-200">
                        @error('address')
                            <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Telefone -->
                    <div class="space-y-1.5">
                        <label for="phone" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Telefone / Contato') }}</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}" placeholder="{{ __('ex: (11) 99999-9999') }}"
                            class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-550 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-200">
                        @error('phone')
                            <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Tipo de biblioteca -->
                    <div class="space-y-1.5">
                        <label for="library_type" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Tipo de biblioteca') }}</label>
                        <select id="library_type" name="library_type"
                            class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-200">
                            <option value="Pessoal" {{ old('library_type') == 'Pessoal' ? 'selected' : '' }}>{{ __('Pessoal') }}</option>
                            <option value="Pública" {{ old('library_type') == 'Pública' ? 'selected' : '' }}>{{ __('Pública') }}</option>
                            <option value="Universitária" {{ old('library_type') == 'Universitária' ? 'selected' : '' }}>{{ __('Universitária') }}</option>
                            <option value="Especializada" {{ old('library_type') == 'Especializada' ? 'selected' : '' }}>{{ __('Especializada') }}</option>
                            <option value="Outro" {{ old('library_type') == 'Outro' ? 'selected' : '' }}>{{ __('Outro') }}</option>
                        </select>
                        @error('library_type')
                            <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status de Funcionamento -->
                    <div class="space-y-1.5">
                        <label for="is_active" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Status de Funcionamento') }} <span class="text-red-500">*</span></label>
                        <select id="is_active" name="is_active" required
                            class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-200">
                            <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>{{ __('Ativa (Disponível para empréstimos e cadastros)') }}</option>
                            <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>{{ __('Inativa (Desativada temporariamente)') }}</option>
                        </select>
                        @error('is_active')
                            <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- SEÇÃO 2: PERSONALIZAÇÃO DO TEMPLATE E MARCA -->
            <div class="space-y-5 pt-4 border-t border-slate-800">
                <h2 class="text-lg font-bold text-slate-100 border-b border-slate-800 pb-3 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-primary">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.098 19.902a3.75 3.75 0 0 0 5.304 0l6.401-6.402M16.5 7.5l3 3m-3-3l-2.25-2.25a2.25 2.25 0 0 0-3.182 0l-5.5 5.5a2.25 2.25 0 0 0 0 3.182l2.25 2.25" />
                    </svg>
                    {{ __('Personalização do Template & Marca') }}
                </h2>

                <!-- Subtítulo da biblioteca -->
                <div class="space-y-1.5">
                    <label for="subtitle" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Subtítulo Personalizado') }}</label>
                    <input type="text" id="subtitle" name="subtitle" value="{{ old('subtitle') }}" placeholder="{{ __('ex: Biblioteca Comunitária Curió (Padrão: Biblioteca Inteligente)') }}"
                        class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-550 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-200">
                    <p class="text-[11px] text-slate-500">{{ __('Exibido logo abaixo da logo na barra lateral e nas páginas da biblioteca.') }}</p>
                </div>

                <!-- Logo da biblioteca -->
                <div class="space-y-2">
                    <label class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Logo da Biblioteca') }}</label>
                    <div class="flex flex-col sm:flex-row items-center gap-4 bg-slate-950 p-4 rounded-2xl border border-slate-800">
                        <img src="/images/bibliologo.jpg" alt="{{ __('Logo Padrão') }}" class="w-24 h-auto max-h-20 object-contain rounded-xl bg-white p-1 shadow">
                        <div class="space-y-2 w-full">
                            <input type="file" name="custom_logo_file" accept="image/*" class="text-xs text-slate-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary file:text-white hover:file:bg-primary-hover cursor-pointer">
                            <input type="url" name="custom_logo_url" value="{{ old('custom_logo_url') }}" placeholder="{{ __('Ou cole a URL direta de uma imagem da logo...') }}"
                                class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-100 focus:outline-none">
                        </div>
                    </div>
                </div>

                <!-- Seletor de Esquema de Cores -->
                <div class="space-y-3">
                    <label class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Esquema de Cores do Template') }}</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach(\App\Models\Library::getColorSchemes() as $key => $scheme)
                            <label onclick="selectColorScheme('{{ $key }}')" class="color-scheme-card relative border rounded-2xl p-3 cursor-pointer transition flex items-center gap-3 select-none {{ old('color_scheme', 'emerald') === $key ? 'border-primary bg-primary/10 ring-2 ring-primary/40' : 'border-slate-800 bg-slate-950 hover:border-slate-700' }}">
                                <input type="radio" name="color_scheme" value="{{ $key }}" class="sr-only" {{ old('color_scheme', 'emerald') === $key ? 'checked' : '' }}>
                                <div class="w-6 h-6 rounded-full flex-shrink-0 border border-white/20 shadow-sm" style="background-color: {{ $scheme['primary'] }}"></div>
                                <div class="truncate">
                                    <span class="block text-xs font-semibold text-slate-100 truncate">{{ __($scheme['name']) }}</span>
                                    <span class="block text-[10px] text-slate-500 font-mono">{{ $scheme['primary'] }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- SEÇÃO 3: COMPARTILHAMENTO & PÁGINA PÚBLICA -->
            <div class="space-y-4 pt-4 border-t border-slate-800">
                <h2 class="text-lg font-bold text-slate-100 border-b border-slate-800 pb-3 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-primary">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                    </svg>
                    {{ __('Compartilhamento & Catálogo Público') }}
                </h2>

                <div class="space-y-1.5">
                    <label for="share_code" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Código de compartilhamento personalizado (Opcional)') }}</label>
                    <input type="text" id="share_code" name="share_code" value="{{ old('share_code') }}" placeholder="{{ __('ex: minhabib (Gerado automaticamente se deixado em branco)') }}"
                        class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-550 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-200">
                    @error('share_code')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Página pública checkbox -->
                <div class="flex items-center pt-2">
                    <input type="checkbox" id="is_public_page" name="is_public_page" value="1" {{ old('is_public_page') ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-slate-800 bg-slate-950 text-blue-600 focus:ring-blue-500 focus:ring-offset-slate-950">
                    <label for="is_public_page" class="text-sm font-medium text-slate-350 ml-2 select-none cursor-pointer">{{ __('Tornar catálogo público? (Disponibiliza um link curto público do acervo)') }}</label>
                    @error('is_public_page')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- SEÇÃO 4: CAMPOS PERSONALIZADOS & NÚMERO DE CHAMADA AUTOMÁTICO -->
            @include('libraries.partials.call_number_builder', ['library' => null, 'isEdit' => false])

            <!-- Action buttons -->
            <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-850">
                <a href="{{ route('libraries.index') }}" class="bg-slate-900 hover:bg-slate-800 text-slate-450 border border-slate-800 font-medium px-6 py-2.5 rounded-xl text-xs transition duration-150">
                    {{ __('Cancelar') }}
                </a>
                <button type="submit" form="library-create-form" class="bg-primary hover:bg-primary-hover text-white font-medium px-8 py-2.5 rounded-xl text-xs transition duration-150 shadow-lg shadow-emerald-950/15">
                    {{ __('Confirmar Cadastro') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const colorSchemes = @json(\App\Models\Library::getColorSchemes());
    function selectColorScheme(key) {
        const radio = document.querySelector(`input[name="color_scheme"][value="${key}"]`);
        if (radio) radio.checked = true;

        document.querySelectorAll('.color-scheme-card').forEach(card => {
            card.classList.remove('border-primary', 'bg-primary/10', 'ring-2', 'ring-primary/40');
            card.classList.add('border-slate-800', 'bg-slate-950');
        });

        if (radio) {
            const activeCard = radio.closest('.color-scheme-card');
            if (activeCard) {
                activeCard.classList.remove('border-slate-800', 'bg-slate-950');
                activeCard.classList.add('border-primary', 'bg-primary/10', 'ring-2', 'ring-primary/40');
            }
        }

        const scheme = colorSchemes[key];
        if (scheme) {
            document.documentElement.style.setProperty('--color-primary', scheme.primary);
            document.documentElement.style.setProperty('--color-primary-hover', scheme.primary_hover);
        }
    }
</script>
@endsection
