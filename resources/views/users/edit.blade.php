@extends('layouts.app')

@section('title', __('Editar Usuário'))

@section('content')
@php
    $isCurator = Auth::user()->role === 'curator';
@endphp
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
        <h1 class="text-3xl font-bold tracking-tight text-slate-100">{{ __('Editar Usuário:') }} {{ $user->name }}</h1>
        <p class="text-sm text-slate-400 mt-1">{{ __('Atualize as informações de cadastro e perfil de permissões.') }}</p>
    </div>

    <!-- Form Container -->
    <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 md:p-8 shadow-xl">
        <form action="{{ route('users.update', $user->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Name -->
                <div class="md:col-span-2 space-y-1.5">
                    <label for="name" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Nome Completo') }} <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" required value="{{ old('name', $user->name) }}" {{ $isCurator ? 'disabled' : '' }}
                        class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 disabled:opacity-60 disabled:text-slate-400 disabled:cursor-not-allowed transition duration-150">
                    @error('name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Email -->
                <div class="md:col-span-2 space-y-1.5">
                    <label for="email" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('E-mail') }} <span class="text-red-500">*</span></label>
                    <input type="email" id="email" name="email" required value="{{ old('email', $user->email) }}" {{ $isCurator ? 'disabled' : '' }}
                        class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 disabled:opacity-60 disabled:text-slate-400 disabled:cursor-not-allowed transition duration-150">
                    @error('email') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Telefone Celular -->
                <div class="md:col-span-2">
                    @include('partials.phone-input', ['value' => $user->phone, 'disabled' => $isCurator])
                </div>

                @if(Auth::user()->isAdmin())
                <!-- Global Role Selection (Admin only) -->
                <div class="space-y-1.5 md:col-span-2">
                    <label for="global_role" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Perfil Principal') }}</label>
                    <select id="global_role" name="global_role" onchange="toggleRoleSelection()" {{ $isCurator ? 'disabled' : '' }}
                        class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 disabled:opacity-60 disabled:text-slate-400 disabled:cursor-not-allowed transition duration-150">
                        <option value="" {{ old('global_role', in_array($user->role, ['admin', 'curator']) ? $user->role : '') === '' ? 'selected' : '' }}>{{ __('Definido por Unidade (Leitor / Atendente / Gestor)') }}</option>
                        <option value="curator" {{ old('global_role', $user->role) === 'curator' ? 'selected' : '' }}>{{ __('Curador (Acesso Global)') }}</option>
                        <option value="admin" {{ old('global_role', $user->role) === 'admin' ? 'selected' : '' }}>{{ __('Administrador (Acesso Global)') }}</option>
                    </select>
                    @error('global_role') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                @endif

                <!-- Dynamic Libraries Search Section -->
                <div id="libraries-section" class="md:col-span-2 space-y-3">
                    <label class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Vincular Unidades / Bibliotecas') }} <span class="text-red-500">*</span></label>
                    
                    @if(!$isCurator)
                    <!-- Search Input -->
                    <div class="relative">
                        <input type="text" id="library-search-input" oninput="searchLibraries()" placeholder="{{ __('Digite o nome da biblioteca para buscar e adicionar...') }}"
                            class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-550 focus:outline-none focus:border-blue-500 transition duration-150">
                        
                        <!-- Search Results Dropdown -->
                        <div id="library-search-results" class="hidden absolute left-0 right-0 mt-2 bg-slate-950 border border-slate-800 rounded-xl divide-y divide-slate-850 max-h-60 overflow-y-auto z-50 shadow-2xl">
                        </div>
                    </div>
                    @endif

                    <!-- Selected Libraries List -->
                    <div id="selected-libraries-list" class="space-y-3 p-4 bg-slate-950/50 rounded-xl border border-slate-800/80 min-h-[80px] flex flex-col justify-center">
                        <p class="text-xs text-slate-500 italic text-center">{{ __('Nenhuma biblioteca selecionada.') }}</p>
                    </div>
                    @error('library_roles') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-between gap-4 pt-4 border-t border-slate-850">
                <div>
                    @if(!$isCurator)
                        <button type="button" onclick="openPasswordModal()" class="bg-slate-800 hover:bg-slate-750 text-slate-200 border border-slate-800 font-medium px-5 py-2.5 rounded-xl text-xs transition duration-150">
                            {{ __('Alterar Senha') }}
                        </button>
                    @endif
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('users.index') }}" class="bg-slate-900 hover:bg-slate-800 text-slate-400 border border-slate-800 font-medium px-6 py-2.5 rounded-xl text-xs transition duration-150">
                        {{ $isCurator ? __('Voltar') : __('Cancelar') }}
                    </a>
                    @if(!$isCurator)
                        <button type="submit" class="bg-primary hover:bg-primary-hover text-white font-medium px-8 py-2.5 rounded-xl text-xs transition duration-150 shadow-lg shadow-emerald-950/15">
                            {{ __('Salvar Alterações') }}
                        </button>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- User Loans History -->
    <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 shadow-xl space-y-4">
        <div>
            <h3 class="text-lg font-bold text-slate-100">{{ __('Histórico de Empréstimos') }}</h3>
            <p class="text-xs text-slate-400 mt-0.5">{{ __('Últimos empréstimos vinculados a este leitor.') }}</p>
        </div>

        @if($loans->isEmpty())
            <div class="text-center py-8">
                <p class="text-slate-500 text-xs italic">{{ __('Nenhum empréstimo registrado para este usuário.') }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-850 text-slate-400 text-xs uppercase tracking-wider font-bold">
                            <th class="py-3 px-4">{{ __('Livro') }}</th>
                            <th class="py-3 px-4">{{ __('Unidade') }}</th>
                            <th class="py-3 px-4">{{ __('Data Empréstimo') }}</th>
                            <th class="py-3 px-4">{{ __('Data Limite') }}</th>
                            <th class="py-3 px-4">{{ __('Data Devolução') }}</th>
                            <th class="py-3 px-4">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-850/50 text-sm">
                        @foreach($loans as $loan)
                            <tr class="hover:bg-slate-900/10 transition duration-150">
                                <!-- Book Link -->
                                <td class="py-3 px-4">
                                    <a href="{{ route('catalog.show', $loan->localCopy->centralItem->id) }}" class="text-blue-400 hover:underline font-semibold">{{ $loan->localCopy->centralItem->title }}</a>
                                    <div class="text-[10px] text-slate-500">{{ __('ISBN:') }} {{ $loan->localCopy->centralItem->isbn ?? __('Sem ISBN') }}</div>
                                </td>
                                
                                <!-- Library -->
                                <td class="py-3 px-4 text-slate-300 text-xs">
                                    {{ $loan->localCopy->library->name }}
                                </td>
                                
                                <!-- Dates -->
                                <td class="py-3 px-4 text-slate-300 text-xs">
                                    {{ $loan->loan_date->format('d/m/Y') }}
                                    <span class="block text-[10px] text-slate-500">{{ __('por') }} {{ $loan->attendant->name }}</span>
                                </td>
                                <td class="py-3 px-4 text-xs font-medium {{ (!$loan->return_date && $loan->due_date->isPast()) ? 'text-red-400' : 'text-slate-300' }}">
                                    {{ $loan->due_date->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-4 text-slate-400 text-xs">
                                    {{ $loan->return_date ? $loan->return_date->format('d/m/Y') : '—' }}
                                </td>
                                
                                <!-- Status -->
                                <td class="py-3 px-4">
                                    @if($loan->return_date)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/15">
                                            {{ __('Devolvido') }}
                                        </span>
                                    @elseif($loan->due_date->isPast())
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-400 border border-red-500/15 animate-pulse">
                                            {{ __('Atrasado') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/15">
                                            {{ __('Emprestado') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 border-t border-slate-850/30 pt-3">
                {{ $loans->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Change Password Modal -->
<div id="passwordModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity" onclick="closePasswordModal()"></div>

    <!-- Container -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-md transform overflow-hidden rounded-3xl bg-slate-900 border border-slate-800 p-6 shadow-2xl transition-all">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-850 pb-4">
                <h3 class="text-base font-bold text-slate-100">{{ __('Alterar Senha do Usuário') }}</h3>
                <button type="button" onclick="closePasswordModal()" class="text-slate-400 hover:text-slate-200 focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <form action="{{ route('users.update-password', $user->id) }}" method="POST" class="space-y-4 pt-4">
                @csrf
                @method('PUT')

                <!-- Password -->
                <div class="space-y-1.5">
                    <label for="modal_password" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Nova Senha') }} <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="password" id="modal_password" name="password" required minlength="8"
                            class="w-full bg-slate-950 border border-slate-800/80 rounded-xl pl-4 pr-10 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-blue-500 transition duration-150">
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

                <!-- Password Confirmation -->
                <div class="space-y-1.5">
                    <label for="modal_password_confirmation" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Confirmar Nova Senha') }} <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="password" id="modal_password_confirmation" name="password_confirmation" required minlength="8"
                            class="w-full bg-slate-950 border border-slate-800/80 rounded-xl pl-4 pr-10 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-blue-500 transition duration-150">
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

                <!-- Modal Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-850">
                    <button type="button" onclick="closePasswordModal()" class="bg-slate-800 hover:bg-slate-750 text-slate-300 font-semibold px-4 py-2 rounded-xl text-xs transition duration-150">
                        {{ __('Cancelar') }}
                    </button>
                    <button type="submit" class="bg-primary hover:bg-primary-hover text-white font-semibold px-5 py-2 rounded-xl text-xs transition duration-150 shadow-lg">
                        {{ __('Salvar Senha') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const transNoLibFound = @json(__('Nenhuma biblioteca encontrada'));
    const transSelect = @json(__('Selecionar'));
    const transNoLibSelected = @json(__('Nenhuma biblioteca selecionada.'));
    const transRoleUser = @json(__('Leitor'));
    const transRoleAttendant = @json(__('Atendente'));
    const transRoleManager = @json(__('Gestor'));
    const transRemove = @json(__('Remover'));

    function openPasswordModal() {
        document.getElementById('passwordModal').classList.remove('hidden');
    }

    function closePasswordModal() {
        document.getElementById('passwordModal').classList.add('hidden');
    }

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
    const isCurator = @json($isCurator);

    let selectedLibraries = [];

    function searchLibraries() {
        if (isCurator) return;
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
            resultsContainer.innerHTML = `<div class="p-3 text-xs text-slate-500 italic text-center">${transNoLibFound}</div>`;
            resultsContainer.classList.remove('hidden');
            return;
        }
        
        resultsContainer.innerHTML = matches.map(lib => `
            <div class="p-3 hover:bg-slate-900/60 cursor-pointer flex justify-between items-center transition" onclick="selectLibrary(${lib.id})">
                <span class="text-sm font-medium text-slate-350">${lib.name}</span>
                <span class="text-xs text-blue-400 font-bold hover:underline">+ ${transSelect}</span>
            </div>
        `).join('');
        resultsContainer.classList.remove('hidden');
    }

    function selectLibrary(id) {
        if (isCurator) return;
        const lib = allLibraries.find(l => l.id === id);
        if (lib && !selectedLibraries.some(selected => selected.id === id)) {
            selectedLibraries.push(lib);
            renderSelectedLibraries();
        }
        document.getElementById('library-search-input').value = '';
        document.getElementById('library-search-results').classList.add('hidden');
    }

    function removeLibrary(id) {
        if (isCurator) return;
        selectedLibraries = selectedLibraries.filter(selected => selected.id !== id);
        renderSelectedLibraries();
    }

    function renderSelectedLibraries() {
        const container = document.getElementById('selected-libraries-list');
        if (selectedLibraries.length === 0) {
            container.innerHTML = `<p class="text-xs text-slate-500 italic text-center">${transNoLibSelected}</p>`;
            return;
        }
        
        container.innerHTML = selectedLibraries.map(lib => {
            let roleOptions = '';
            if (isAdmin) {
                roleOptions = `
                    <option value="user">${transRoleUser}</option>
                    <option value="attendant">${transRoleAttendant}</option>
                    <option value="manager">${transRoleManager}</option>
                `;
            } else if (userRole === 'manager') {
                roleOptions = `
                    <option value="user">${transRoleUser}</option>
                    <option value="attendant">${transRoleAttendant}</option>
                    <option value="manager">${transRoleManager}</option>
                `;
            } else {
                roleOptions = `
                    <option value="user">${transRoleUser}</option>
                `;
            }
            
            const disabledAttr = isCurator ? 'disabled' : '';
            const removeBtn = isCurator ? '' : `
                <button type="button" onclick="removeLibrary(${lib.id})" class="text-xs text-red-400 hover:text-red-300 font-bold transition">
                    ${transRemove}
                </button>
            `;
            
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
                        <select name="library_roles[${lib.id}]" ${disabledAttr} class="bg-slate-950 border border-slate-800 rounded-lg px-2.5 py-1 text-xs text-slate-200 focus:outline-none focus:border-blue-500 disabled:opacity-55 transition">
                            ${roleOptions}
                        </select>
                        ${removeBtn}
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
            inputs.forEach(i => {
                if (!isCurator) {
                    i.disabled = false;
                }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const oldIds = @json(old('library_ids'));
        const oldRoles = @json(old('library_roles', []));
        
        if (oldIds !== null) {
            oldIds.forEach(id => {
                const lib = allLibraries.find(l => l.id == id);
                if (lib) {
                    selectedLibraries.push(lib);
                }
            });
        } else {
            // Load user's current libraries
            const currentLibraries = @json($user->libraries->map(function($lib) {
                return [
                    'id' => $lib->id,
                    'name' => $lib->name
                ];
            }));
            
            currentLibraries.forEach(cl => {
                const lib = allLibraries.find(l => l.id == cl.id);
                if (lib) {
                    selectedLibraries.push(lib);
                }
            });
        }
        
        renderSelectedLibraries();
        
        // Set roles
        selectedLibraries.forEach(lib => {
            const select = document.querySelector(`select[name="library_roles[${lib.id}]"]`);
            if (select) {
                if (oldIds !== null) {
                    select.value = oldRoles[lib.id] || 'user';
                } else {
                    // Find current pivot role
                    const currentLib = @json($user->libraries->map(function($lib) {
                        return [
                            'id' => $lib->id,
                            'pivot_role' => $lib->pivot->role ?? 'user'
                        ];
                    })).find(cl => cl.id == lib.id);
                    select.value = currentLib ? currentLib.pivot_role : 'user';
                }
            }
        });

        if (document.getElementById('global_role')) {
            toggleRoleSelection();
        }
    });
</script>
@endsection

