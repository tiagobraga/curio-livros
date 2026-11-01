@extends('layouts.app')

@section('title', __('Novo Usuário'))

@section('content')
<div class="space-y-8">
    <!-- Back Link -->
    <div>
        <a href="{{ route('users.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-slate-200 transition duration-150">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            {{ __('Voltar para a lista') }}
        </a>
    </div>

    <!-- Header -->
    <div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-100">{{ __('Cadastrar Usuário') }}</h1>
        <p class="text-sm text-slate-400 mt-1">{{ __('Crie credenciais para leitores ou membros da equipe da biblioteca.') }}</p>
    </div>

    <!-- Form Container -->
    <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 md:p-8 shadow-xl">
        <form action="{{ route('users.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Name -->
                <div class="md:col-span-2 space-y-1.5">
                    <label for="name" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Nome Completo') }} <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" required value="{{ old('name') }}"
                        class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                    @error('name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Email -->
                <div class="md:col-span-2 space-y-1.5">
                    <label for="email" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('E-mail') }} <span class="text-red-500">*</span></label>
                    <input type="email" id="email" name="email" required value="{{ old('email') }}"
                        class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                    @error('email') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Telefone Celular -->
                <div class="md:col-span-2">
                    @include('partials.phone-input')
                </div>

                @if(Auth::user()->isAdmin())
                <!-- Global Role Selection (Admin only) -->
                <div class="space-y-1.5 md:col-span-2">
                    <label for="global_role" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Perfil Principal') }}</label>
                    <select id="global_role" name="global_role" onchange="toggleRoleSelection()"
                        class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                        <option value="" {{ old('global_role') === '' ? 'selected' : '' }}>{{ __('Definido por Unidade (Leitor / Atendente / Gestor)') }}</option>
                        <option value="curator" {{ old('global_role') === 'curator' ? 'selected' : '' }}>{{ __('Curador (Acesso Global)') }}</option>
                        <option value="admin" {{ old('global_role') === 'admin' ? 'selected' : '' }}>{{ __('Administrador (Acesso Global)') }}</option>
                    </select>
                    @error('global_role') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                @endif

                <!-- Dynamic Libraries Search Section -->
                <div id="libraries-section" class="md:col-span-2 space-y-3">
                    <label class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Vincular Unidades / Bibliotecas') }} <span class="text-red-500">*</span></label>
                    
                    <!-- Search Input -->
                    <div class="relative">
                        <input type="text" id="library-search-input" oninput="searchLibraries()" placeholder="{{ __('Digite o nome da biblioteca para buscar...') }}"
                            class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-blue-500 transition duration-150">
                        
                        <!-- Search Results Dropdown -->
                        <div id="library-search-results" class="hidden absolute left-0 right-0 mt-2 bg-slate-950 border border-slate-800 rounded-xl divide-y divide-slate-850 max-h-60 overflow-y-auto z-50 shadow-2xl">
                        </div>
                    </div>

                    <!-- Selected Libraries List -->
                    <div id="selected-libraries-list" class="space-y-3 p-4 bg-slate-950/50 rounded-xl border border-slate-800/80 min-h-[80px] flex flex-col justify-center">
                        <p class="text-xs text-slate-500 italic text-center">{{ __('Nenhuma biblioteca selecionada.') }}</p>
                    </div>
                    @error('library_roles') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Password -->
                <div class="space-y-1.5">
                    <label for="password" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Senha') }} <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required
                            class="w-full bg-slate-950 border border-slate-800/80 rounded-xl pl-4 pr-10 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                        <button type="button" onclick="togglePasswordVisibility('password', this)" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-200 focus:outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 eye-icon">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 hidden eye-slash-icon">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                    @error('password') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Password Confirmation -->
                <div class="space-y-1.5">
                    <label for="password_confirmation" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Confirmar Senha') }} <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="password" id="password_confirmation" name="password_confirmation" required
                            class="w-full bg-slate-950 border border-slate-800/80 rounded-xl pl-4 pr-10 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                        <button type="button" onclick="togglePasswordVisibility('password_confirmation', this)" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-200 focus:outline-none">
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

            <!-- Buttons -->
            <div class="flex items-center justify-end gap-4 pt-4 border-t border-slate-850">
                <a href="{{ route('users.index') }}" class="bg-slate-900 hover:bg-slate-800 text-slate-450 border border-slate-800 font-medium px-6 py-2.5 rounded-xl text-xs transition duration-150">
                    {{ __('Cancelar') }}
                </a>
                <button type="submit" class="bg-primary hover:bg-primary-hover text-white font-medium px-8 py-2.5 rounded-xl text-xs transition duration-150 shadow-lg shadow-emerald-950/15">
                    {{ __('Salvar Usuário') }}
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

    const allLibraries = @json($libraries);
    const userRole = @json(Auth::user()->role);
    const isAdmin = @json(Auth::user()->isAdmin());

    let selectedLibraries = [];

    function searchLibraries() {
        const query = document.getElementById('library-search-input').value.toLowerCase();
        const resultsContainer = document.getElementById('library-search-results');
        
        if (query.length < 2) {
            resultsContainer.innerHTML = '';
            resultsContainer.classList.add('hidden');
            return;
        }
        
        const matches = allLibraries.filter(lib => 
            lib.name.toLowerCase().includes(query) && 
            !selectedLibraries.some(selected => selected.id === lib.id)
        );
        
        if (matches.length === 0) {
            resultsContainer.innerHTML = '<div class="p-3 text-xs text-slate-500 italic text-center">' + @json(__('Nenhuma biblioteca encontrada')) + '</div>';
            resultsContainer.classList.remove('hidden');
            return;
        }
        
        resultsContainer.innerHTML = matches.map(lib => `
            <div class="p-3 hover:bg-slate-900/60 cursor-pointer flex justify-between items-center transition" onclick="selectLibrary(${lib.id})">
                <span class="text-sm font-medium text-slate-350">${lib.name}</span>
                <span class="text-xs text-blue-400 font-bold hover:underline">+ ${@json(__('Selecionar'))}</span>
            </div>
        `).join('');
        resultsContainer.classList.remove('hidden');
    }

    function selectLibrary(id) {
        const lib = allLibraries.find(l => l.id === id);
        if (lib && !selectedLibraries.some(selected => selected.id === id)) {
            selectedLibraries.push(lib);
            renderSelectedLibraries();
        }
        document.getElementById('library-search-input').value = '';
        document.getElementById('library-search-results').classList.add('hidden');
    }

    function removeLibrary(id) {
        selectedLibraries = selectedLibraries.filter(selected => selected.id !== id);
        renderSelectedLibraries();
    }

    function renderSelectedLibraries() {
        const container = document.getElementById('selected-libraries-list');
        if (selectedLibraries.length === 0) {
            container.innerHTML = '<p class="text-xs text-slate-500 italic text-center">' + @json(__('Nenhuma biblioteca selecionada.')) + '</p>';
            return;
        }
        
        container.innerHTML = selectedLibraries.map(lib => {
            let roleOptions = '';
            if (isAdmin) {
                roleOptions = `
                    <option value="user">${@json(__('Leitor'))}</option>
                    <option value="attendant">${@json(__('Atendente'))}</option>
                    <option value="manager">${@json(__('Gestor'))}</option>
                `;
            } else if (userRole === 'manager') {
                roleOptions = `
                    <option value="user">${@json(__('Leitor'))}</option>
                    <option value="attendant">${@json(__('Atendente'))}</option>
                `;
            } else {
                roleOptions = `
                    <option value="user">${@json(__('Leitor'))}</option>
                `;
            }
            
            return `
                <div class="flex items-center justify-between p-3.5 bg-slate-900/40 rounded-xl border border-slate-850 hover:border-slate-850 transition">
                    <div class="flex items-center gap-2">
                        <input type="hidden" name="library_ids[]" value="${lib.id}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-slate-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.33l-7.5-5-7.5 5V21m3.75 0h7.5" />
                        </svg>
                        <span class="text-sm font-medium text-slate-200">${lib.name}</span>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <select name="library_roles[${lib.id}]" class="bg-slate-950 border border-slate-800 rounded-lg px-2.5 py-1 text-xs text-slate-200 focus:outline-none focus:border-blue-500 transition">
                            ${roleOptions}
                        </select>
                        <button type="button" onclick="removeLibrary(${lib.id})" class="text-xs text-red-400 hover:text-red-300 font-bold transition">
                            ${@json(__('Remover'))}
                        </button>
                    </div>
                </div>
            `;
        }).join('');
    }

    function toggleRoleSelection() {
        const globalRoleSelect = document.getElementById('global_role');
        const librariesSection = document.getElementById('libraries-section');
        if (globalRoleSelect && globalRoleSelect.value !== '') {
            librariesSection.classList.add('hidden');
            const inputs = librariesSection.querySelectorAll('input, select');
            inputs.forEach(i => i.disabled = true);
        } else if (librariesSection) {
            librariesSection.classList.remove('hidden');
            const inputs = librariesSection.querySelectorAll('input, select');
            inputs.forEach(i => i.disabled = false);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const oldIds = @json(old('library_ids', []));
        const oldRoles = @json(old('library_roles', []));
        
        oldIds.forEach(id => {
            const lib = allLibraries.find(l => l.id == id);
            if (lib) {
                selectedLibraries.push(lib);
            }
        });
        
        renderSelectedLibraries();
        
        // Set old roles values
        Object.keys(oldRoles).forEach(libId => {
            const select = document.querySelector(`select[name="library_roles[${libId}]"]`);
            if (select) {
                select.value = oldRoles[libId];
            }
        });

        if (document.getElementById('global_role')) {
            toggleRoleSelection();
        }
    });
</script>
@endsection
