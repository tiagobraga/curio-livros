@extends('layouts.app')

@section('title', __('Gerenciamento de Usuários'))

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-100">{{ __('Gerenciamento de Usuários') }}</h1>
            <p class="text-sm text-slate-400 mt-1">{{ __('Cadastre e gerencie leitores e equipe de atendimento da biblioteca.') }}</p>
        </div>
        
        @if(Auth::user()->role !== 'curator')
        <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white font-medium px-4 py-2.5 rounded-xl text-sm transition duration-150 shadow-lg shadow-emerald-950/15">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.75-7.75h-15" />
            </svg>
            {{ __('Novo Usuário') }}
        </a>
        @endif
    </div>

    @if(isset($pendingRequests) && $pendingRequests->isNotEmpty())
        <!-- Solitações de Vínculo Pendentes -->
        <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 shadow-xl space-y-4">
            <div>
                <h2 class="text-base font-bold text-slate-100 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-amber-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286Zm0 13.036h.008v.008H12v-.008Z" />
                    </svg>
                    {{ __('Fila de Moderação: Solicitações de Vínculo Pendentes') }}
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">{{ __('Aprove ou rejeite as solicitações de vínculo com as bibliotecas sob sua responsabilidade.') }}</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-850 text-slate-400 text-xs font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">{{ __('Usuário') }}</th>
                            <th class="py-3 px-4">{{ __('Papel') }}</th>
                            <th class="py-3 px-4">{{ __('Biblioteca Solicitada') }}</th>
                            <th class="py-3 px-4">{{ __('Data da Solicitação') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('Ações') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-850/60 text-sm">
                        @foreach($pendingRequests as $req)
                            <tr class="hover:bg-slate-900/20 transition duration-150">
                                <td class="py-3 px-4 font-semibold text-slate-200">
                                    <div class="flex flex-col">
                                        <span>{{ $req->user->name }}</span>
                                        <span class="text-xs text-slate-500 font-normal">{{ $req->user->email }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold {{ $req->user->role === 'manager' ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' : ($req->user->role === 'attendant' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-blue-500/10 text-blue-400 border border-blue-500/20') }}">
                                        {{ $req->user->role === 'manager' ? __('Gestor') : ($req->user->role === 'attendant' ? __('Atendente') : __('Leitor')) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-300">{{ $req->library->name }}</td>
                                <td class="py-3 px-4 text-slate-400 text-xs">{{ $req->created_at->format('d/m/Y H:i') }}</td>
                                <td class="py-3 px-4 text-right">
                                    <div class="inline-flex gap-2">
                                        <form action="{{ route('users.requests.approve', $req->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="bg-emerald-950/40 text-emerald-400 border border-emerald-900/50 hover:bg-emerald-900/20 px-3 py-1.5 rounded-lg text-xs font-semibold transition duration-150">
                                                {{ __('Aprovar') }}
                                            </button>
                                        </form>
                                        <form action="{{ route('users.requests.reject', $req->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="bg-red-950/40 text-red-400 border border-red-900/50 hover:bg-red-900/20 px-3 py-1.5 rounded-lg text-xs font-semibold transition duration-150">
                                                {{ __('Rejeitar') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Filters & Search -->
    <div class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
        <!-- Role Filter Tabs -->
        <div class="flex flex-wrap gap-1.5 p-1 bg-slate-900/60 backdrop-blur rounded-xl border border-slate-800/80">
            <a href="{{ route('users.index', ['role' => '', 'search' => $search]) }}" 
                class="px-4 py-1.5 rounded-lg text-xs font-semibold transition duration-150 {{ !$roleFilter ? 'bg-primary text-white shadow-md' : 'text-slate-400 hover:text-slate-200' }}">
                {{ __('Todos') }}
            </a>
            <a href="{{ route('users.index', ['role' => 'user', 'search' => $search]) }}" 
                class="px-4 py-1.5 rounded-lg text-xs font-semibold transition duration-150 {{ $roleFilter === 'user' ? 'bg-primary text-white shadow-md' : 'text-slate-400 hover:text-slate-200' }}">
                {{ __('Leitores') }}
            </a>
            <a href="{{ route('users.index', ['role' => 'attendant', 'search' => $search]) }}" 
                class="px-4 py-1.5 rounded-lg text-xs font-semibold transition duration-150 {{ $roleFilter === 'attendant' ? 'bg-primary text-white shadow-md' : 'text-slate-400 hover:text-slate-200' }}">
                {{ __('Atendentes') }}
            </a>
            @if(Auth::user()->isAdmin())
                <a href="{{ route('users.index', ['role' => 'manager', 'search' => $search]) }}" 
                    class="px-4 py-1.5 rounded-lg text-xs font-semibold transition duration-150 {{ $roleFilter === 'manager' ? 'bg-primary text-white shadow-md' : 'text-slate-400 hover:text-slate-200' }}">
                    {{ __('Gestores') }}
                </a>
                <a href="{{ route('users.index', ['role' => 'admin', 'search' => $search]) }}" 
                    class="px-4 py-1.5 rounded-lg text-xs font-semibold transition duration-150 {{ $roleFilter === 'admin' ? 'bg-primary text-white shadow-md' : 'text-slate-400 hover:text-slate-200' }}">
                    {{ __('Administradores') }}
                </a>
            @endif
        </div>

        <!-- Search Box -->
        <form action="{{ route('users.index') }}" method="GET" class="flex gap-2 w-full sm:w-auto">
            @if($roleFilter)
                <input type="hidden" name="role" value="{{ $roleFilter }}">
            @endif
            <div class="relative flex-1 sm:w-60">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="absolute left-3 top-2.5 w-4 h-4 text-slate-500">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input type="text" name="search" value="{{ $search }}"
                    placeholder="{{ __('Buscar por nome ou e-mail...') }}" 
                    class="w-full bg-slate-900/40 border border-slate-800 rounded-xl pl-9 pr-4 py-2 text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-blue-500 transition duration-200">
            </div>
            <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700/80 font-medium px-4 py-2 rounded-xl text-xs transition duration-150">
                {{ __('Buscar') }}
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 shadow-xl">
        @if($users->isEmpty())
            <div class="text-center py-16">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-16 h-16 text-slate-700 mx-auto mb-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.109A2.25 2.25 0 0 1 12.75 21.5h-1.5a2.25 2.25 0 0 1-2.25-2.263V19.13m4.786-3.07M9.786 16.06a8.96 8.96 0 0 0-4.786-3.07M9.786 16.06A8.961 8.961 0 0 1 15 13c1.381 0 2.677.312 3.834.869m-8.154 2.191A8.961 8.961 0 0 0 5 13M5 13a9.06 9.06 0 0 0-2.625.372 9.337 9.337 0 0 0-4.121-.952 4.125 4.125 0 0 0 7.533-2.493M5 13v.109a2.25 2.25 0 0 0 2.25 2.263h1.5a2.25 2.25 0 0 0 2.25-2.263v-.109m0 0V14.1" />
                </svg>
                <h3 class="text-lg font-semibold text-slate-300">{{ __('Nenhum usuário cadastrado') }}</h3>
                <p class="text-sm text-slate-500 mt-1">{{ __('Nenhum registro corresponde aos filtros selecionados.') }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-850 text-slate-400 text-xs uppercase tracking-wider font-bold">
                            <th class="py-4 px-4">{{ __('Nome') }}</th>
                            <th class="py-4 px-4">{{ __('E-mail') }}</th>
                            <th class="py-4 px-4">{{ __('Papel (Permissões)') }}</th>
                            <th class="py-4 px-4">{{ __('Biblioteca / Unidade') }}</th>
                            <th class="py-4 px-4 text-right">{{ __('Ações') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-850/50 text-sm">
                        @foreach($users as $u)
                            <tr class="hover:bg-slate-900/10 transition duration-150">
                                <td class="py-4 px-4 font-semibold text-slate-200">
                                    {{ $u->name }}
                                    @if(Auth::user()->id === $u->id)
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-blue-500/10 text-blue-400 border border-blue-500/15">
                                            {{ __('Você') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-slate-400">{{ $u->email }}</td>
                                <td class="py-4 px-4">
                                    @if($u->role === 'admin')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-500/10 text-red-400 border border-red-500/15">
                                            {{ __('Administrador') }}
                                        </span>
                                    @elseif($u->role === 'manager')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-purple-500/10 text-purple-400 border border-purple-500/15">
                                            {{ __('Gestor') }}
                                        </span>
                                    @elseif($u->role === 'curator')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-pink-500/10 text-pink-400 border border-pink-500/15">
                                            {{ __('Curador') }}
                                        </span>
                                    @elseif($u->role === 'attendant')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/15">
                                            {{ __('Atendente') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/15">
                                            {{ __('Leitor') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-slate-300">
                                    @if($u->libraries->isNotEmpty())
                                        {{ $u->libraries->pluck('name')->implode(', ') }}
                                    @else
                                        {{ __('Global / Curadoria Central') }}
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        @if(Auth::user()->canEditUser($u))
                                            <a href="{{ route('users.edit', $u->id) }}" 
                                                class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 px-3 py-1.5 rounded-lg text-xs font-semibold transition duration-150">
                                                {{ Auth::user()->role === 'curator' ? __('Visualizar') : __('Editar') }}
                                            </a>

                                            @if(Auth::user()->role !== 'curator' && Auth::user()->id !== $u->id)
                                                <form action="{{ route('users.destroy', $u->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" onclick="return confirm('{{ __('Deseja realmente excluir este usuário? Todos os empréstimos vinculados a ele serão impactados.') }}')" 
                                                        class="bg-red-950/40 text-red-400 border border-red-900/50 hover:bg-red-900/20 px-3 py-1.5 rounded-lg text-xs font-semibold transition duration-150">
                                                        {{ __('Excluir') }}
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            <span class="text-xs text-slate-600 font-medium">—</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
