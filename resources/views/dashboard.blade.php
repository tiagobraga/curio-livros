@extends('layouts.app')

@section('title', __('Dashboard'))

@section('content')
@if(Auth::user()->isLibraryUser())
<div class="space-y-8">
    <!-- Welcome Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-100">{{ __('Olá') }}, {{ Auth::user()->name }}!</h1>
            <p class="text-sm text-slate-400 mt-1">
                @if($library)
                    {{ __('Você está associado à(s) unidade(s):') }} <span class="text-emerald-700 font-bold dark:text-emerald-400">{{ $library->name }}</span>.
                @else
                    {{ __('Nenhuma biblioteca associada ao seu perfil. Entre em contato com o administrador.') }}
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white font-medium px-4 py-2.5 rounded-xl text-sm transition duration-150 shadow-lg shadow-emerald-950/15">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                {{ __('Buscar Livros no Catálogo') }}
            </a>
        </div>
    </div>

    <!-- METRICS GRID -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <!-- Card 1: Total Books in user's Libraries -->
        <div class="bg-slate-900/40 backdrop-blur border border-slate-800/80 rounded-2xl p-6 flex items-center justify-between shadow-lg">
            <div class="space-y-1">
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Livros Disponíveis na(s) sua(s) unidade(s)') }}</span>
                <span class="block text-3xl font-bold text-slate-100">{{ $totalBooks }}</span>
            </div>
            <div class="p-3 bg-blue-500/10 text-blue-400 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
            </div>
        </div>

        <!-- Card 2: User's Active Loans -->
        <div class="bg-slate-900/40 backdrop-blur border border-slate-800/80 rounded-2xl p-6 flex items-center justify-between shadow-lg">
            <div class="space-y-1">
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Meus Empréstimos Ativos') }}</span>
                <span class="block text-3xl font-bold text-slate-100">{{ $myLoans->whereNull('return_date')->count() }}</span>
            </div>
            <div class="p-3 bg-indigo-500/10 text-indigo-400 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0-4.5 4.5M21 7.5H7.5" />
                </svg>
            </div>
        </div>
    </div>

    <!-- MAIN TWO-COLUMN LAYOUT -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- LEFT COLUMN: Loans and Recent Books -->
        <div class="lg:col-span-2 space-y-8">
            <!-- MY LOANS -->
            <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 shadow-xl">
                <h2 class="text-xl font-bold tracking-tight text-slate-100 mb-6">{{ __('Meus Empréstimos') }}</h2>
                
                @if($myLoans->isEmpty())
                    <div class="text-center py-12 border border-dashed border-slate-800 rounded-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 text-slate-700 mx-auto mb-3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                        </svg>
                        <p class="text-slate-500 text-sm">{{ __('Você não possui empréstimos registrados.') }}</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-850 text-slate-400 text-xs uppercase tracking-wider font-bold">
                                    <th class="py-4 px-4">{{ __('Livro') }}</th>
                                    <th class="py-4 px-4">{{ __('Data Empréstimo') }}</th>
                                    <th class="py-4 px-4">{{ __('Prazo Devolução') }}</th>
                                    <th class="py-4 px-4">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-850/50 text-sm">
                                @foreach($myLoans as $loan)
                                    <tr class="hover:bg-slate-900/20 transition duration-150">
                                        <td class="py-4 px-4">
                                            <div class="font-medium text-slate-200">{{ $loan->localCopy->centralItem->title }}</div>
                                            <div class="text-xs text-slate-500">{{ __('ISBN:') }} {{ $loan->localCopy->centralItem->isbn ?? __('Sem ISBN') }}</div>
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
                @endif
            </div>

            <!-- RECENT BOOKS -->
            <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 shadow-xl">
                <h2 class="text-xl font-bold tracking-tight text-slate-100 mb-6">{{ __('Novidades do Acervo (10 Mais Recentes)') }}</h2>
                
                @if($recentBooks->isEmpty())
                    <p class="text-slate-500 text-sm text-center py-8">{{ __('Nenhum livro recém-cadastrado na sua unidade.') }}</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($recentBooks as $book)
                            @php
                                $meta = $book->metadata;
                                $author = $meta['author'] ?? __('Autor desconhecido');
                                $cover = $meta['cover_url'] ?? null;
                            @endphp
                            <a href="{{ route('catalog.show', $book->id) }}" class="flex gap-4 p-3 bg-slate-900/20 hover:bg-slate-900/40 rounded-2xl border border-slate-850 hover:border-slate-700 transition duration-150">
                                <div class="w-12 h-16 bg-slate-950 rounded-lg overflow-hidden border border-slate-800 flex-shrink-0 flex items-center justify-center">
                                    @if($cover)
                                        <img src="{{ $cover }}" alt="Capa" class="w-full h-full object-cover">
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-slate-700">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1 flex flex-col justify-center">
                                    <h4 class="font-bold text-sm text-slate-100 truncate">{{ $book->title }}</h4>
                                    <p class="text-xs text-slate-400 truncate">{{ $author }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- RIGHT COLUMN: Ratings & Comments -->
        <div class="space-y-8">
            <!-- TOP RATED BOOKS -->
            <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 shadow-xl">
                <h2 class="text-xl font-bold tracking-tight text-slate-100 mb-6">{{ __('Mais Bem Avaliados') }}</h2>
                
                @if($topRatedBooks->isEmpty())
                    <p class="text-slate-500 text-sm text-center py-8">{{ __('Nenhum livro avaliado ainda.') }}</p>
                @else
                    <div class="space-y-4">
                        @foreach($topRatedBooks as $book)
                            @php
                                $meta = $book->metadata;
                                $author = $meta['author'] ?? __('Autor desconhecido');
                                $cover = $meta['cover_url'] ?? null;
                            @endphp
                            <a href="{{ route('catalog.show', $book->id) }}" class="flex items-center gap-4 p-3 bg-slate-900/20 hover:bg-slate-900/40 rounded-2xl border border-slate-850 hover:border-slate-700 transition duration-150">
                                <div class="w-10 h-14 bg-slate-950 rounded-lg overflow-hidden border border-slate-800 flex-shrink-0 flex items-center justify-center">
                                    @if($cover)
                                        <img src="{{ $cover }}" alt="Capa" class="w-full h-full object-cover">
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-700">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-bold text-xs text-slate-100 truncate">{{ $book->title }}</h4>
                                    <div class="flex items-center gap-1 mt-1">
                                        <div class="flex text-amber-400">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5 {{ $i <= round($book->reviews_avg_rating ?? 0) ? 'text-amber-400' : 'text-slate-700' }}">
                                                    <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd" />
                                                </svg>
                                            @endfor
                                        </div>
                                        <span class="text-[10px] text-slate-500 font-bold">({{ number_format($book->reviews_avg_rating ?? 0, 1) }})</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- COMMUNITY COMMENTS -->
            <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 shadow-xl">
                <h2 class="text-xl font-bold tracking-tight text-slate-100 mb-6">{{ __('Comentários dos Leitores') }}</h2>
                
                @if($recentComments->isEmpty())
                    <p class="text-slate-500 text-sm text-center py-8">{{ __('Nenhum comentário registrado nos livros mais avaliados.') }}</p>
                @else
                    <div class="space-y-4">
                        @foreach($recentComments as $comment)
                            <div class="p-4 bg-slate-900/20 rounded-2xl border border-slate-850 space-y-2">
                                <div class="flex justify-between items-start">
                                    <div class="min-w-0">
                                        <span class="block text-xs font-semibold text-slate-200 truncate">{{ $comment->user->name }}</span>
                                        <span class="block text-[10px] text-slate-500">{{ __('sobre') }} <a href="{{ route('catalog.show', $comment->centralItem->id) }}" class="text-blue-400 hover:underline">{{ $comment->centralItem->title }}</a></span>
                                    </div>
                                    <div class="flex text-amber-400 flex-shrink-0">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3 h-3 {{ $i <= $comment->rating ? 'text-amber-400' : 'text-slate-700' }}">
                                                <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd" />
                                            </svg>
                                        @endfor
                                    </div>
                                </div>
                                <p class="text-xs text-slate-400 italic font-light line-clamp-3">"{{ $comment->comment }}"</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@else
<div class="space-y-8">
    <!-- Welcome Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-100">{{ __('Olá') }}, {{ Auth::user()->name }}!</h1>
            <p class="text-sm text-slate-400 mt-1">
                @if($library)
                    {{ __('Você está operando no acervo da unidade:') }} <span class="text-emerald-700 font-bold dark:text-emerald-400">{{ $library->name }}</span>.
                @else
                    {{ __('Visão global do sistema. Curadoria de acervos activa.') }}
                @endif
            </p>
        </div>
        
        <!-- Actions shortcuts -->
        <div class="flex items-center gap-3">
            @if(Auth::user()->isAttendant())
                <a href="{{ route('catalog.create') }}" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white font-medium px-4 py-2.5 rounded-xl text-sm transition duration-150 shadow-lg shadow-emerald-950/15">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.75-7.75h-15" />
                    </svg>
                    {{ __('Novo Livro (Scanner)') }}
                </a>
                <a href="{{ route('loans.index') }}" class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700/80 font-medium px-4 py-2.5 rounded-xl text-sm transition duration-150">
                    {{ __('Novo Empréstimo') }}
                </a>
            @endif
        </div>
    </div>

    <!-- METRICS GRID -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1: Total Books -->
        <div class="bg-slate-900/40 backdrop-blur border border-slate-800/80 rounded-2xl p-6 flex items-center justify-between shadow-lg">
            <div class="space-y-1">
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Total de Livros') }}</span>
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

        <!-- Card 4: Users -->
        <div class="bg-slate-900/40 backdrop-blur border border-slate-800/80 rounded-2xl p-6 flex items-center justify-between shadow-lg">
            <div class="space-y-1">
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Usuários') }}</span>
                <span class="block text-3xl font-bold text-slate-100">{{ $totalUsers }}</span>
            </div>
            <div class="p-3 bg-amber-500/10 text-amber-400 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.109A2.25 2.25 0 0 1 12.75 21.5h-1.5a2.25 2.25 0 0 1-2.25-2.263V19.13m4.786-3.07M9.786 16.06a8.96 8.96 0 0 0-4.786-3.07M9.786 16.06A8.961 8.961 0 0 1 15 13c1.381 0 2.677.312 3.834.869m-8.154 2.191A8.961 8.961 0 0 0 5 13M5 13a9.06 9.06 0 0 0-2.625.372 9.337 9.337 0 0 0-4.121-.952 4.125 4.125 0 0 0 7.533-2.493M5 13v.109a2.25 2.25 0 0 0 2.25 2.263h1.5a2.25 2.25 0 0 0 2.25-2.263v-.109m0 0V14.1" />
                </svg>
            </div>
        </div>
    </div>

    <!-- SECOND ROW: CIRCULATION CHART -->
    <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-slate-100">{{ __('Fluxo de Circulação') }}</h2>
                <p class="text-xs text-slate-400 mt-1">{{ __('Empréstimos vs Devoluções diárias nos últimos 30 dias.') }}</p>
            </div>
            <div class="flex items-center gap-4 text-xs font-semibold">
                <span class="flex items-center gap-1.5 text-blue-400">
                    <span class="w-3 h-3 bg-blue-500 rounded-full"></span> {{ __('Empréstimos') }}
                </span>
                <span class="flex items-center gap-1.5 text-emerald-400">
                    <span class="w-3 h-3 bg-emerald-500 rounded-full"></span> {{ __('Devoluções') }}
                </span>
            </div>
        </div>
        
        <div class="h-64 relative">
            <canvas id="circulationChart"></canvas>
        </div>
    </div>

    <!-- RECENT ACTIVITY (TRANSACTIONS) -->
    <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-slate-100">{{ __('Transações Recentes') }}</h2>
                <p class="text-xs text-slate-400 mt-1">{{ __('Últimos eventos e atualizações no sistema.') }}</p>
            </div>
            
            <a href="{{ route('transactions.index') }}" class="inline-flex items-center gap-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700/80 font-bold px-4 py-2.5 rounded-xl text-xs transition duration-150">
                {{ __('Ver todas as transações →') }}
            </a>
        </div>
        
        @if($recentTransactions->isEmpty())
            <div class="text-center py-12 border border-dashed border-slate-800 rounded-2xl">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 text-slate-700 mx-auto mb-3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
                <p class="text-slate-500 text-sm">{{ __('Nenhuma transação registrada recentemente.') }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-850 text-slate-400 text-xs uppercase tracking-wider font-bold">
                            <th class="py-4 px-4">{{ __('Tipo') }}</th>
                            <th class="py-4 px-4">{{ __('Descrição') }}</th>
                            <th class="py-4 px-4">{{ __('Unidade') }}</th>
                            <th class="py-4 px-4">{{ __('Data / Hora') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-850/50 text-sm">
                        @foreach($recentTransactions as $tx)
                            <tr class="hover:bg-slate-900/20 transition duration-150">
                                <td class="py-4 px-4">
                                    @if($tx->type === 'loan')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/15">
                                            {{ __('Empréstimo') }}
                                        </span>
                                    @elseif($tx->type === 'return')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/15">
                                            {{ __('Devolução') }}
                                        </span>
                                    @elseif($tx->type === 'overdue')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-500/10 text-red-400 border border-red-500/15 animate-pulse">
                                            {{ __('Atraso') }}
                                        </span>
                                    @elseif($tx->type === 'new_book')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-500/10 text-purple-400 border border-purple-500/15">
                                            {{ __('Novo Livro') }}
                                        </span>
                                    @elseif($tx->type === 'new_user')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/15">
                                            {{ __('Novo Usuário') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-slate-200">
                                    @if($tx->type === 'loan' || $tx->type === 'return' || $tx->type === 'overdue')
                                        @if($tx->book_id && $tx->user_id)
                                            {{ __('Prazo de devolução de') }} '
                                            <a href="{{ route('catalog.show', $tx->book_id) }}" class="text-blue-400 hover:underline font-medium">{{ $tx->book_title }}</a>
                                            ' {{ $tx->type === 'loan' ? __('emprestado para o leitor') : ($tx->type === 'return' ? __('devolvido pelo leitor') : __('expirou para o leitor')) }}
                                            @if(Auth::user()->isManager())
                                                <a href="{{ route('users.edit', $tx->user_id) }}" class="text-blue-400 hover:underline font-medium">{{ $tx->user_name }}</a>.
                                            @else
                                                <span class="font-medium text-slate-300">{{ $tx->user_name }}</span>.
                                            @endif
                                        @else
                                            {{ $tx->description }}
                                        @endif
                                    @elseif($tx->type === 'new_book')
                                        @if($tx->book_id)
                                            {{ __('Novo exemplar de') }} '
                                            <a href="{{ route('catalog.show', $tx->book_id) }}" class="text-blue-400 hover:underline font-medium">{{ $tx->book_title }}</a>
                                            ' {{ __('cadastrado na unidade') }} {{ $tx->library_name }}.
                                        @else
                                            {{ $tx->description }}
                                        @endif
                                    @elseif($tx->type === 'new_user')
                                        @if($tx->user_id)
                                            {{ __('Novo leitor') }} 
                                            @if(Auth::user()->isManager())
                                                <a href="{{ route('users.edit', $tx->user_id) }}" class="text-blue-400 hover:underline font-medium">{{ $tx->user_name }}</a>
                                            @else
                                                <span class="font-medium text-slate-300">{{ $tx->user_name }}</span>
                                            @endif
                                            {{ __('cadastrado no sistema.') }}
                                        @else
                                            {{ $tx->description }}
                                        @endif
                                    @else
                                        {{ $tx->description }}
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-xs text-slate-400">
                                    {{ $tx->library_name }}
                                </td>
                                <td class="py-4 px-4 text-xs text-slate-400">
                                    {{ $tx->date->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endif
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('circulationChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartLabels ?? []) !!},
                datasets: [
                    {
                        label: 'Empréstimos',
                        data: {!! json_encode($loansChartValues ?? []) !!},
                        backgroundColor: '#3b82f6',
                        borderRadius: 6,
                        borderSkipped: false,
                    },
                    {
                        label: 'Devoluções',
                        data: {!! json_encode($returnsChartValues ?? []) !!},
                        backgroundColor: '#10b981',
                        borderRadius: 6,
                        borderSkipped: false,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: '#0f172a',
                        titleColor: '#f1f5f9',
                        bodyColor: '#cbd5e1',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 10,
                        bodyFont: {
                            family: 'Inter, system-ui, sans-serif',
                            size: 12
                        },
                        titleFont: {
                            family: 'Inter, system-ui, sans-serif',
                            weight: 'bold',
                            size: 13
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#94a3b8',
                            font: {
                                size: 10
                            }
                        }
                    },
                    y: {
                        grid: {
                            color: '#334155',
                            drawTicks: false
                        },
                        ticks: {
                            color: '#94a3b8',
                            font: {
                                size: 10
                            },
                            precision: 0
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
