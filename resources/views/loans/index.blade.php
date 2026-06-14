@extends('layouts.app')

@section('title', 'Circulação & Empréstimos')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-100">Circulação & Empréstimos</h1>
            <p class="text-sm text-slate-400 mt-1">Gerencie a saída e retorno de livros das unidades físicas.</p>
        </div>
        
        <a href="{{ route('loans.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white font-medium px-4 py-2.5 rounded-xl text-sm transition duration-150 shadow-lg shadow-blue-500/15">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.75-7.75h-15" />
            </svg>
            Novo Empréstimo
        </a>
    </div>

    <!-- Filters and Search -->
    <div class="flex flex-col lg:flex-row gap-4 justify-between items-start lg:items-center">
        <!-- Status Filters (Tabs-like style) -->
        <div class="flex flex-wrap gap-1.5 p-1 bg-slate-900/60 backdrop-blur rounded-xl border border-slate-800/80">
            <a href="{{ route('loans.index', ['status' => 'all', 'search' => $search]) }}" 
                class="px-4 py-1.5 rounded-lg text-xs font-semibold transition duration-150 {{ (!$statusFilter || $statusFilter === 'all') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200' }}">
                Todos
            </a>
            <a href="{{ route('loans.index', ['status' => 'active', 'search' => $search]) }}" 
                class="px-4 py-1.5 rounded-lg text-xs font-semibold transition duration-150 {{ $statusFilter === 'active' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200' }}">
                Emprestados
            </a>
            <a href="{{ route('loans.index', ['status' => 'late', 'search' => $search]) }}" 
                class="px-4 py-1.5 rounded-lg text-xs font-semibold transition duration-150 {{ $statusFilter === 'late' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200' }}">
                Atrasados
            </a>
            <a href="{{ route('loans.index', ['status' => 'returned', 'search' => $search]) }}" 
                class="px-4 py-1.5 rounded-lg text-xs font-semibold transition duration-150 {{ $statusFilter === 'returned' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200' }}">
                Devolvidos
            </a>
        </div>

        <!-- Search Form -->
        <form action="{{ route('loans.index') }}" method="GET" class="flex gap-2 w-full lg:w-auto max-w-md">
            @if($statusFilter)
                <input type="hidden" name="status" value="{{ $statusFilter }}">
            @endif
            <div class="relative flex-1 lg:w-64">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="absolute left-3 top-2.5 w-4 h-4 text-slate-500">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input type="text" name="search" value="{{ $search }}"
                    placeholder="Buscar livro ou leitor..." 
                    class="w-full bg-slate-900/40 border border-slate-800 rounded-xl pl-9 pr-4 py-2 text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-blue-500 transition duration-200">
            </div>
            <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700/80 font-medium px-4 py-2 rounded-xl text-xs transition duration-150">
                Buscar
            </button>
            @if($search)
                <a href="{{ route('loans.index', ['status' => $statusFilter]) }}" class="bg-slate-900 hover:bg-slate-800 text-slate-400 border border-slate-800 font-medium px-3 py-2 rounded-xl text-xs transition duration-150 flex items-center justify-center">
                    Limpar
                </a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 shadow-xl">
        @if($loans->isEmpty())
            <div class="text-center py-16">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-16 h-16 text-slate-700 mx-auto mb-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0-4.5 4.5M21 7.5H7.5" />
                </svg>
                <h3 class="text-lg font-semibold text-slate-300">Nenhum empréstimo registrado</h3>
                <p class="text-sm text-slate-500 mt-1">Nenhum registro corresponde aos filtros selecionados.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-850 text-slate-400 text-xs uppercase tracking-wider font-bold">
                            <th class="py-4 px-4">Livro</th>
                            <th class="py-4 px-4">Leitor</th>
                            <th class="py-4 px-4">Data Empréstimo</th>
                            <th class="py-4 px-4">Data Limite</th>
                            <th class="py-4 px-4">Data Devolução</th>
                            <th class="py-4 px-4">Status</th>
                            <th class="py-4 px-4 text-right">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-850/50 text-sm">
                        @foreach($loans as $loan)
                            <tr class="hover:bg-slate-900/10 transition duration-150">
                                <!-- Book Info -->
                                <td class="py-4 px-4">
                                    <div class="font-semibold text-slate-200">{{ $loan->localCopy->centralItem->title }}</div>
                                    <div class="text-xs text-slate-500">ISBN: {{ $loan->localCopy->centralItem->isbn ?? 'Sem ISBN' }}</div>
                                    <div class="text-[10px] text-blue-400">{{ $loan->localCopy->library->name }}</div>
                                </td>
                                
                                <!-- Reader Info -->
                                <td class="py-4 px-4">
                                    <div class="text-slate-200 font-medium">{{ $loan->user->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $loan->user->email }}</div>
                                </td>
                                
                                <!-- Dates -->
                                <td class="py-4 px-4 text-slate-300 text-xs">
                                    {{ $loan->loan_date->format('d/m/Y') }}
                                    <span class="block text-[10px] text-slate-500">por {{ $loan->attendant->name }}</span>
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
                                            Devolvido
                                        </span>
                                    @elseif($loan->due_date->isPast())
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-400 border border-red-500/15 animate-pulse">
                                            Atrasado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/15">
                                            Emprestado
                                        </span>
                                    @endif
                                </td>
                                
                                <!-- Return Action Button -->
                                <td class="py-4 px-4 text-right">
                                    @if(!$loan->return_date)
                                        <form action="{{ route('loans.return', $loan->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" onclick="return confirm('Deseja confirmar a devolução deste exemplar?')" 
                                                class="bg-emerald-600 hover:bg-emerald-500 text-white font-semibold px-3 py-1.5 rounded-lg text-xs transition duration-150 shadow-md shadow-emerald-600/10">
                                                Devolver
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
