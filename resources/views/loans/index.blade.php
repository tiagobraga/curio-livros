@extends('layouts.app')

@section('title', __('Circulação & Empréstimos'))

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-100">{{ __('Circulação & Empréstimos') }}</h1>
            <p class="text-sm text-slate-400 mt-1">{{ __('Gerencie a saída e retorno de livros das unidades físicas.') }}</p>
        </div>
        
        <a href="{{ route('loans.create') }}" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white font-medium px-4 py-2.5 rounded-xl text-sm transition duration-150 shadow-lg shadow-emerald-950/15">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.75-7.75h-15" />
            </svg>
            {{ __('Novo Empréstimo') }}
        </a>
    </div>
       <!-- METRICS GRID -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1: Total Books -->
        <div class="bg-slate-900/40 backdrop-blur border border-slate-800/80 rounded-2xl p-6 flex items-center justify-between shadow-lg">
            <div class="space-y-1">
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Total de Livros (Exemplares)') }}</span>
                <span class="block text-3xl font-bold text-slate-100">{{ $totalBooks }}</span>
            </div>
            <div class="p-3 bg-blue-500/10 text-blue-400 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
            </div>
        </div>

        <!-- Card 2: Active Loans -->
        <div class="bg-slate-900/40 backdrop-blur border border-slate-800/80 rounded-2xl p-6 flex items-center justify-between shadow-lg">
            <div class="space-y-1">
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Empréstimos Ativos') }}</span>
                <span class="block text-3xl font-bold text-slate-100">{{ $activeLoansCount }}</span>
            </div>
            <div class="p-3 bg-indigo-500/10 text-indigo-400 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0-4.5 4.5M21 7.5H7.5" />
                </svg>
            </div>
        </div>

        <!-- Card 3: Late Loans -->
        <div class="bg-slate-900/40 backdrop-blur border border-slate-800/80 rounded-2xl p-6 flex items-center justify-between shadow-lg {{ $lateLoansCount > 0 ? 'border-red-900/50 bg-red-950/5' : '' }}">
            <div class="space-y-1">
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Atrasados') }}</span>
                <span class="block text-3xl font-bold {{ $lateLoansCount > 0 ? 'text-red-400' : 'text-slate-100' }}">{{ $lateLoansCount }}</span>
            </div>
            <div class="p-3 {{ $lateLoansCount > 0 ? 'bg-red-500/15 text-red-400' : 'bg-slate-800 text-slate-400' }} rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
            </div>
        </div>

        <!-- Card 4: Recent Loans -->
        <div class="bg-slate-900/40 backdrop-blur border border-slate-800/80 rounded-2xl p-6 flex items-center justify-between shadow-lg">
            <div class="space-y-1">
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Últimos 30 Dias') }}</span>
                <span class="block text-3xl font-bold text-slate-100">{{ $recentLoansCount }}</span>
            </div>
            <div class="p-3 bg-amber-500/10 text-amber-400 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 shadow-xl">
        <form action="{{ route('loans.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            @if($statusFilter)
                <input type="hidden" name="status" value="{{ $statusFilter }}">
            @endif

            <!-- Library Filter -->
            <div class="space-y-1.5">
                <label for="library_id" class="block text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('Biblioteca / Unidade') }}</label>
                <select id="library_id" name="library_id" 
                    class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150 cursor-pointer">
                    <option value="">{{ __('Todas as Unidades') }}</option>
                    @foreach($libraries as $lib)
                        <option value="{{ $lib->id }}" {{ $libraryFilter == $lib->id ? 'selected' : '' }}>
                            {{ $lib->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Search Field -->
            <div class="space-y-1.5">
                <label for="search" class="block text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('Buscar Livro ou Leitor') }}</label>
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="absolute left-3 top-3 w-4 h-4 text-slate-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <input type="text" id="search" name="search" value="{{ $search }}"
                        placeholder="{{ __('Nome, ISBN, E-mail...') }}" 
                        class="w-full bg-slate-950 border border-slate-800/80 rounded-xl pl-9 pr-4 py-2.5 text-xs text-slate-100 placeholder-slate-550 focus:outline-none focus:border-blue-500 transition duration-200">
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="flex gap-2">
                <button type="submit" class="bg-primary hover:bg-primary-hover text-white font-medium px-5 py-2.5 rounded-xl text-xs transition duration-150 flex-1 shadow-lg">
                    {{ __('Filtrar') }}
                </button>
                @if($libraryFilter || $search)
                    <a href="{{ route('loans.index', ['status' => $statusFilter]) }}" class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700/80 font-medium px-4 py-2.5 rounded-xl text-xs transition duration-150 flex items-center justify-center">
                        {{ __('Limpar') }}
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Status Filters (Tabs-like style) -->
    <div class="flex flex-wrap gap-1.5 p-1 bg-slate-900/60 backdrop-blur rounded-xl border border-slate-800/80 w-fit">
        <a href="{{ route('loans.index', ['status' => 'active', 'search' => $search, 'library_id' => $libraryFilter]) }}" 
            class="px-4 py-1.5 rounded-lg text-xs font-semibold transition duration-150 {{ $statusFilter === 'active' ? 'bg-primary text-white shadow-md' : 'text-slate-400 hover:text-slate-200' }}">
            {{ __('Emprestados') }}
        </a>
        <a href="{{ route('loans.index', ['status' => 'late', 'search' => $search, 'library_id' => $libraryFilter]) }}" 
            class="px-4 py-1.5 rounded-lg text-xs font-semibold transition duration-150 {{ $statusFilter === 'late' ? 'bg-primary text-white shadow-md' : 'text-slate-400 hover:text-slate-200' }}">
            {{ __('Atrasados') }}
        </a>
        <a href="{{ route('loans.index', ['status' => 'returned', 'search' => $search, 'library_id' => $libraryFilter]) }}" 
            class="px-4 py-1.5 rounded-lg text-xs font-semibold transition duration-150 {{ $statusFilter === 'returned' ? 'bg-primary text-white shadow-md' : 'text-slate-400 hover:text-slate-200' }}">
            {{ __('Devolvidos') }}
        </a>
        <a href="{{ route('loans.index', ['status' => 'all', 'search' => $search, 'library_id' => $libraryFilter]) }}" 
            class="px-4 py-1.5 rounded-lg text-xs font-semibold transition duration-150 {{ ($statusFilter === 'all' || !$statusFilter) ? 'bg-primary text-white shadow-md' : 'text-slate-400 hover:text-slate-200' }}">
            {{ __('Todos') }}
        </a>
    </div>

    <!-- Table -->
    <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 shadow-xl">
        @if($loans->isEmpty())
            <div class="text-center py-16">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-16 h-16 text-slate-700 mx-auto mb-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0-4.5 4.5M21 7.5H7.5" />
                </svg>
                <h3 class="text-lg font-semibold text-slate-300">{{ __('Nenhum empréstimo registrado') }}</h3>
                <p class="text-sm text-slate-500 mt-1">{{ __('Nenhum registro corresponde aos filtros selecionados.') }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-850 text-slate-400 text-xs uppercase tracking-wider font-bold">
                            <th class="py-4 px-4">{{ __('Livro') }}</th>
                            <th class="py-4 px-4">{{ __('Leitor') }}</th>
                            <th class="py-4 px-4">{{ __('Data Empréstimo') }}</th>
                            <th class="py-4 px-4">{{ __('Data Limite') }}</th>
                            <th class="py-4 px-4">{{ __('Data Devolução') }}</th>
                            <th class="py-4 px-4">{{ __('Status') }}</th>
                            <th class="py-4 px-4 text-right">{{ __('Ação') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-850/50 text-sm">
                        @foreach($loans as $loan)
                            <tr class="hover:bg-slate-900/10 transition duration-150">
                                <!-- Book Info -->
                                <td class="py-4 px-4">
                                    <div class="font-semibold text-slate-200">
                                        <a href="{{ route('catalog.show', $loan->localCopy->centralItem->id) }}" class="text-blue-400 hover:underline font-semibold">{{ $loan->localCopy->centralItem->title }}</a>
                                    </div>
                                    <div class="text-xs text-slate-500">{{ __('ISBN:') }} {{ $loan->localCopy->centralItem->isbn ?? __('Sem ISBN') }}</div>
                                    <div class="text-[10px] text-blue-400">{{ $loan->localCopy->library->name }}</div>
                                </td>
                                
                                <!-- Reader Info -->
                                <td class="py-4 px-4">
                                    <div class="font-medium text-slate-200">
                                        @if(Auth::user()->canEditUser($loan->user))
                                            <a href="{{ route('users.edit', $loan->user->id) }}" class="text-blue-400 hover:underline font-semibold">{{ $loan->user->name }}</a>
                                        @else
                                            <span class="text-slate-200 font-medium">{{ $loan->user->name }}</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-500">{{ $loan->user->email }}</div>
                                </td>
                                
                                <!-- Dates -->
                                <td class="py-4 px-4 text-slate-300 text-xs">
                                    {{ $loan->loan_date->format('d/m/Y') }}
                                    <span class="block text-[10px] text-slate-500">{{ __('por') }} {{ $loan->attendant->name }}</span>
                                </td>
                                <td class="py-4 px-4 text-xs font-medium {{ (!$loan->return_date && $loan->due_date->isPast()) ? 'text-red-400' : 'text-slate-300' }}">
                                    {{ $loan->due_date->format('d/m/Y') }}
                                </td>
                                <td class="py-4 px-4 text-slate-400 text-xs">
                                    {{ $loan->return_date ? $loan->return_date->format('d/m/Y') : '—' }}
                                </td>
                                
                                <!-- Status Badge -->
                                <td class="py-4 px-4">
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
                                
                                <!-- Return Action Button -->
                                <td class="py-4 px-4 text-right">
                                    @if(!$loan->return_date)
                                        <form action="{{ route('loans.return', $loan->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" onclick="return confirm('{{ __('Deseja confirmar a devolução deste exemplar?') }}')" 
                                                class="bg-emerald-600 hover:bg-emerald-500 text-white font-semibold px-3 py-1.5 rounded-lg text-xs transition duration-150 shadow-md shadow-emerald-600/10">
                                                {{ __('Devolver') }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-slate-600 text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $loans->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

