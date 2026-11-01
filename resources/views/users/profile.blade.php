@extends('layouts.app')

@section('title', __('Configurações de Perfil'))

@section('content')
<div class="space-y-8 max-w-4xl mx-auto">
    <!-- Header -->
    <div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-100">{{ __('Configurações de Perfil') }}</h1>
        <p class="text-sm text-slate-400 mt-1">{{ __('Gerencie suas informações pessoais e credenciais de acesso ao Biblió.') }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Form Container (Left Column) -->
        <div class="lg:col-span-2 bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 md:p-8 shadow-xl space-y-6">
            <h2 class="text-lg font-bold text-slate-200 border-b border-slate-800 pb-3 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-emerald-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                {{ __('Dados Pessoais') }}
            </h2>

            <form action="{{ route('profile.update') }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div class="space-y-1.5">
                    <label for="name" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Nome Completo') }} <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" required value="{{ old('name', $user->name) }}"
                        class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                    @error('name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Email -->
                <div class="space-y-1.5">
                    <label for="email" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('E-mail') }} <span class="text-red-500">*</span></label>
                    <input type="email" id="email" name="email" required value="{{ old('email', $user->email) }}"
                        class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                    @error('email') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Telefone Celular -->
                @include('partials.phone-input', ['value' => $user->phone])

                <div class="border-t border-slate-800/50 pt-5 mt-5 space-y-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-300 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-emerald-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.33l-7.5-5-7.5 5V21m3.75 0h7.5" />
                            </svg>
                            {{ __('Solicitar Vínculo com Nova Biblioteca') }}
                        </h3>
                        <p class="text-xs text-slate-400 mt-1">{{ __('Busque unidades ativas pelo nome para enviar uma solicitação de acesso.') }}</p>
                    </div>

                    <!-- Search Input -->
                    <div class="relative">
                        <input type="text" id="library-search-input" oninput="filterLibrariesForRequest()" placeholder="{{ __('Digite o nome da biblioteca...') }}"
                            class="w-full bg-slate-950 border border-slate-800/80 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-blue-500 transition duration-150">
                        <div class="absolute left-3.5 top-3.5 text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Search Results -->
                    <div id="search-results-container" class="space-y-2 max-h-60 overflow-y-auto hidden bg-slate-950/40 p-3 rounded-xl border border-slate-800/60">
                        <!-- Filled by JS -->
                    </div>
                </div>

                <div class="flex justify-between items-center pt-4 border-t border-slate-800/80">
                    <button type="button" onclick="openPasswordModal()" class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-800 font-semibold px-5 py-2.5 rounded-xl text-sm transition duration-150">
                        {{ __('Alterar Minha Senha') }}
                    </button>
                    <button type="submit" class="bg-primary hover:bg-primary-hover text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition duration-150 shadow-md shadow-emerald-950/20">
                        {{ __('Salvar Alterações') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Scope & Info (Right Column) -->
        <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 md:p-8 shadow-xl space-y-6 flex flex-col justify-between">
            <div class="space-y-6">
                <h2 class="text-lg font-bold text-slate-200 border-b border-slate-800 pb-3 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-emerald-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                    </svg>
                    {{ __('Vínculo & Escopo') }}
                </h2>

                <!-- Libraries static block -->
                <div class="space-y-2">
                    <span class="block text-xs uppercase font-bold text-slate-500 tracking-wider">{{ __('Bibliotecas Associadas') }}</span>
                    @if($user->libraries->count() > 0)
                        <div class="space-y-2.5">
                            @foreach($user->libraries as $lib)
                                <div class="flex items-center justify-between text-sm text-slate-300 gap-2 p-2 rounded-xl bg-slate-950/40 border border-slate-800/50">
                                    <div class="flex items-center gap-2 truncate">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-slate-500 flex-shrink-0">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.33l-7.5-5-7.5 5V21m3.75 0h7.5" />
                                        </svg>
                                        <span class="truncate font-semibold">{{ $lib->name }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <span class="text-[9px] px-2 py-0.5 rounded font-bold uppercase tracking-wider bg-slate-800 text-slate-400 border border-slate-700/60">
                                            {{ $lib->pivot->role === 'manager' ? __('Gestor') : ($lib->pivot->role === 'attendant' ? __('Atendente') : __('Leitor')) }}
                                        </span>
                                        @if($user->isCurator() || $user->isManagerOf($lib->id))
                                            <a href="{{ route('libraries.edit', $lib->id) }}" class="bg-primary hover:bg-primary-hover text-white text-[10px] font-bold px-2.5 py-1 rounded-lg transition duration-150 shadow-sm">
                                                {{ __('Editar') }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <span class="text-xs text-slate-400 italic">{{ __('Nenhuma biblioteca vinculada (Acesso Global).') }}</span>
                    @endif
                </div>

                <!-- Personal Library Creation Button -->
                <div class="pt-2 mt-6">
                    <a href="{{ route('libraries.create') }}" class="w-full inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary-hover text-white font-semibold px-4 py-2.5 rounded-xl text-sm transition duration-150 shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.75-7.75h-15" />
                        </svg>
                        {{ __('Criar uma biblioteca') }}
                    </a>
                </div>

                <!-- Solicitações pendentes block -->
                @if($pendingRequests->isNotEmpty())
                    <div class="space-y-2 border-t border-slate-800/50 pt-5 mt-5">
                        <span class="block text-xs uppercase font-bold text-slate-500 tracking-wider">{{ __('Solicitações de Vínculo Pendentes') }}</span>
                        <div class="space-y-2">
                            @foreach($pendingRequests as $req)
                                <div class="flex items-center justify-between p-2.5 bg-slate-950/50 rounded-xl border border-slate-800/60 text-xs">
                                    <div class="flex items-center gap-2 text-slate-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 text-amber-500">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        {{ $req->library->name }}
                                    </div>
                                    <span class="text-[10px] px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20 font-medium">{{ __('Pendente') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

<!-- Password Change Modal -->
<div id="passwordModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-slate-950/60 backdrop-blur-sm p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div>
            <h3 class="text-lg font-bold text-slate-100">{{ __('Alterar Senha') }}</h3>
            <p class="text-xs text-slate-400 mt-1">{{ __('Insira e confirme sua nova senha para atualizar suas credenciais.') }}</p>
        </div>

        <form action="{{ route('profile.update-password') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="space-y-1.5">
                <label for="modal_password" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Nova Senha') }}</label>
                <div class="relative">
                    <input type="password" id="modal_password" name="password" required minlength="8"
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-4 pr-10 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition">
                    <button type="button" onclick="togglePasswordVisibility('modal_password', this)" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-200 focus:outline-none">
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

            <div class="space-y-1.5">
                <label for="modal_password_confirmation" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Confirmar Nova Senha') }}</label>
                <div class="relative">
                    <input type="password" id="modal_password_confirmation" name="password_confirmation" required minlength="8"
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-4 pr-10 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition">
                    <button type="button" onclick="togglePasswordVisibility('modal_password_confirmation', this)" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-200 focus:outline-none">
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

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closePasswordModal()" class="bg-slate-950 text-slate-400 hover:text-slate-200 border border-slate-850 px-4 py-2 rounded-lg text-xs font-semibold transition">
                    {{ __('Cancelar') }}
                </button>
                <button type="submit" class="bg-primary hover:bg-primary-hover text-white px-5 py-2 rounded-lg text-xs font-semibold transition shadow-md">
                    {{ __('Alterar Senha') }}
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
        if (input && input.type === 'password') {
            input.type = 'text';
            eyeIcon.classList.add('hidden');
            eyeSlashIcon.classList.remove('hidden');
        } else if (input) {
            input.type = 'password';
            eyeIcon.classList.remove('hidden');
            eyeSlashIcon.classList.add('hidden');
        }
    }

    function openPasswordModal() {
        document.getElementById('passwordModal').classList.remove('hidden');
    }
    
    function closePasswordModal() {
        document.getElementById('passwordModal').classList.add('hidden');
    }
    
    @php
        $searchableLibraries = $allLibraries->reject(function($lib) use ($linkedIds, $pendingIds) {
            return in_array($lib->id, $linkedIds) || in_array($lib->id, $pendingIds);
        })->values()->map(function($lib) {
            return [
                'id' => $lib->id,
                'name' => $lib->name
            ];
        });
    @endphp

    const availableLibraries = @json($searchableLibraries);
    
    function filterLibrariesForRequest() {
        const input = document.getElementById('library-search-input');
        const container = document.getElementById('search-results-container');
        const query = input.value.trim().toLowerCase();
        
        if (query.length === 0) {
            container.innerHTML = '';
            container.classList.add('hidden');
            return;
        }
        
        const matches = availableLibraries.filter(lib => {
            return lib.name.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").includes(
                query.normalize("NFD").replace(/[\u0300-\u036f]/g, "")
            );
        });
        
        if (matches.length === 0) {
            container.innerHTML = '<div class="text-xs text-slate-500 italic p-2 text-center">{{ __("Nenhuma biblioteca encontrada.") }}</div>';
            container.classList.remove('hidden');
            return;
        }
        
        let html = '';
        matches.forEach(lib => {
            html += `
                <div class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-900/60 transition">
                    <span class="text-xs font-semibold text-slate-300">${lib.name}</span>
                    <form action="{{ route('profile.request-library') }}" method="POST" class="m-0">
                        @csrf
                        <input type="hidden" name="library_id" value="${lib.id}">
                        <button type="submit" class="bg-primary/15 hover:bg-primary hover:text-white text-emerald-400 font-semibold px-2.5 py-1 rounded-lg text-[10px] transition duration-150 border border-emerald-500/20">
                            {{ __('Solicitar acesso') }}
                        </button>
                    </form>
                </div>
            `;
        });
        
        container.innerHTML = html;
        container.classList.remove('hidden');
    }
</script>
@endsection
