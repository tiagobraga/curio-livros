@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-8">
    <!-- Welcome Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-100">Olá, {{ Auth::user()->name }}!</h1>
            <p class="text-sm text-slate-400 mt-1">
                @if($library)
                    Você está operando no acervo da unidade: <span class="text-blue-400 font-medium">{{ $library->name }}</span>.
                @else
                    Visão global do sistema. Curadoria de acervos ativa.
                @endif
            </p>
        </div>
        
        <!-- Actions shortcuts -->
        <div class="flex items-center gap-3">
            @if(Auth::user()->isAttendant())
                <a href="{{ route('catalog.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white font-medium px-4 py-2.5 rounded-xl text-sm transition duration-150 shadow-lg shadow-blue-500/15">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.75-7.75h-15" />
                    </svg>
                    Novo Livro (Scanner)
                </a>
                <a href="{{ route('loans.index') }}" class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700/80 font-medium px-4 py-2.5 rounded-xl text-sm transition duration-150">
                    Novo Empréstimo
                </a>
            @endif
        </div>
    </div>

    <!-- METRICS GRID -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1: Total Books -->
        <div class="bg-slate-900/40 backdrop-blur border border-slate-800/80 rounded-2xl p-6 flex items-center justify-between shadow-lg">
            <div class="space-y-1">
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Total de Livros</span>
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
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Empréstimos Ativos</span>
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
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Atrasados</span>
                <span class="block text-3xl font-bold {{ $lateLoansCount > 0 ? 'text-red-400' : 'text-slate-100' }}">{{ $lateLoansCount }}</span>
            </div>
            <div class="p-3 {{ $lateLoansCount > 0 ? 'bg-red-500/15 text-red-400' : 'bg-slate-800 text-slate-400' }} rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
            </div>
        </div>

        <!-- Card 4: Users -->
        <div class="bg-slate-900/40 backdrop-blur border border-slate-800/80 rounded-2xl p-6 flex items-center justify-between shadow-lg">
            <div class="space-y-1">
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Usuários</span>
                <span class="block text-3xl font-bold text-slate-100">{{ $totalUsers }}</span>
            </div>
            <div class="p-3 bg-amber-500/10 text-amber-400 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.109A2.25 2.25 0 0 1 12.75 21.5h-1.5a2.25 2.25 0 0 1-2.25-2.263V19.13m4.786-3.07M9.786 16.06a8.96 8.96 0 0 0-4.786-3.07M9.786 16.06A8.961 8.961 0 0 1 15 13c1.381 0 2.677.312 3.834.869m-8.154 2.191A8.961 8.961 0 0 0 5 13M5 13a9.06 9.06 0 0 0-2.625.372 9.337 9.337 0 0 0-4.121-.952 4.125 4.125 0 0 0 7.533-2.493M5 13v.109a2.25 2.25 0 0 0 2.25 2.263h1.5a2.25 2.25 0 0 0 2.25-2.263v-.109m0 0V14.1" />
                </svg>
            </div>
        </div>
    </div>

    <!-- RECENT ACTIVITY -->
    <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 shadow-xl">
        <h2 class="text-xl font-bold tracking-tight text-slate-100 mb-6">Empréstimos Recentes</h2>
        
        @if($recentLoans->isEmpty())
            <div class="text-center py-12 border border-dashed border-slate-800 rounded-2xl">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 text-slate-700 mx-auto mb-3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
                <p class="text-slate-500 text-sm">Nenhum empréstimo registrado recentemente.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-850 text-slate-400 text-xs uppercase tracking-wider font-bold">
                            <th class="py-4 px-4">Livro</th>
                            <th class="py-4 px-4">Leitor</th>
                            <th class="py-4 px-4">Data Empréstimo</th>
                            <th class="py-4 px-4">Data Devolução</th>
                            <th class="py-4 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-850/50 text-sm">
                        @foreach($recentLoans as $loan)
                            <tr class="hover:bg-slate-900/20 transition duration-150">
                                <td class="py-4 px-4">
                                    <div class="font-medium text-slate-200">{{ $loan->localCopy->centralItem->title }}</div>
                                    <div class="text-xs text-slate-500">ISBN: {{ $loan->localCopy->centralItem->isbn ?? 'Sem ISBN' }}</div>
                                </td>
                                <td class="py-4 px-4 text-slate-300">
                                    {{ $loan->user->name }}
                                </td>
                                <td class="py-4 px-4 text-slate-400">
                                    {{ $loan->loan_date->format('d/m/Y') }}
                                </td>
                                <td class="py-4 px-4 text-slate-400">
                                    {{ $loan->due_date->format('d/m/Y') }}
                                </td>
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
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
