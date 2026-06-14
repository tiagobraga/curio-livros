@extends('layouts.app')

@section('title', 'Editar Usuário')

@section('content')
<div class="space-y-8">
    <!-- Back Link -->
    <div>
        <a href="{{ route('users.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-slate-200 transition duration-150">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Voltar para a lista
        </a>
    </div>

    <!-- Header -->
    <div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-100">Editar Usuário: {{ $user->name }}</h1>
        <p class="text-sm text-slate-400 mt-1">Atualize as informações de cadastro e perfil de permissões.</p>
    </div>

    <!-- Form Container -->
    <div class="max-w-2xl bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 md:p-8 shadow-xl">
        <form action="{{ route('users.update', $user->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Name -->
                <div class="md:col-span-2 space-y-1.5">
                    <label for="name" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">Nome Completo <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" required value="{{ old('name', $user->name) }}"
                        class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                    @error('name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Email -->
                <div class="md:col-span-2 space-y-1.5">
                    <label for="email" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">E-mail <span class="text-red-500">*</span></label>
                    <input type="email" id="email" name="email" required value="{{ old('email', $user->email) }}"
                        class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                    @error('email') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Role -->
                <div class="space-y-1.5">
                    <label for="role" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">Papel / Perfil <span class="text-red-500">*</span></label>
                    <select id="role" name="role" required
                        class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                        @foreach($roles as $value => $label)
                            <option value="{{ $value }}" {{ old('role', $user->role) === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('role') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Library / Branch -->
                <div class="space-y-1.5">
                    <label for="library_id" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">Unidade Vinculada</label>
                    <select id="library_id" name="library_id" 
                        class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                        @if(Auth::user()->isAdmin())
                            <option value="">Nenhuma (Global / Curadoria Central)</option>
                        @endif
                        @foreach($libraries as $lib)
                            <option value="{{ $lib->id }}" {{ old('library_id', $user->library_id) == $lib->id ? 'selected' : '' }}>
                                {{ $lib->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('library_id') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Password Info Alert -->
                <div class="md:col-span-2 p-3 bg-slate-950 border border-slate-850 rounded-xl text-slate-500 text-xs leading-relaxed">
                    Deixe os campos de senha em branco caso não queira alterar a senha atual do usuário.
                </div>

                <!-- Password -->
                <div class="space-y-1.5">
                    <label for="password" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">Nova Senha</label>
                    <input type="password" id="password" name="password"
                        class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                    @error('password') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Password Confirmation -->
                <div class="space-y-1.5">
                    <label for="password_confirmation" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">Confirmar Nova Senha</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end gap-4 pt-4 border-t border-slate-850">
                <a href="{{ route('users.index') }}" class="bg-slate-900 hover:bg-slate-800 text-slate-400 border border-slate-800 font-medium px-6 py-2.5 rounded-xl text-xs transition duration-150">
                    Cancelar
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-medium px-8 py-2.5 rounded-xl text-xs transition duration-150 shadow-lg shadow-blue-500/15">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
