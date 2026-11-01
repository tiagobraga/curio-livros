@extends('layouts.app')

@section('title', 'Histórico de Transações')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-100">Histórico de Transações</h1>
            <p class="text-sm text-slate-400 mt-1">Monitore empréstimos, devoluções, novos leitores e livros cadastrados.</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 shadow-xl">
        <form action="{{ route('transactions.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <!-- Library Filter -->
            <div class="space-y-1.5">
                <label for="library_id" class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Biblioteca / Unidade</label>
                <select id="library_id" name="library_id" 
                    class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150 cursor-pointer">
                    <option value="">Todas as Unidades</option>
                    @foreach($libraries as $lib)
                        <option value="{{ $lib->id }}" {{ $libraryFilter == $lib->id ? 'selected' : '' }}>
                            {{ $lib->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Type Filter -->
            <div class="space-y-1.5">
                <label for="type" class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Tipo de Transação</label>
                <select id="type" name="type" 
                    class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150 cursor-pointer">
                    <option value="">Todos os tipos</option>
                    <option value="loan" {{ $typeFilter === 'loan' ? 'selected' : '' }}>Empréstimos</option>
                    <option value="return" {{ $typeFilter === 'return' ? 'selected' : '' }}>Devoluções</option>
                    <option value="overdue" {{ $typeFilter === 'overdue' ? 'selected' : '' }}>Itens em Atraso</option>
                    <option value="new_user" {{ $typeFilter === 'new_user' ? 'selected' : '' }}>Novos Usuários</option>
                    <option value="new_book" {{ $typeFilter === 'new_book' ? 'selected' : '' }}>Novos Livros</option>
                </select>
            </div>

            <!-- Filter Buttons -->
            <div class="flex gap-2">
                <button type="submit" class="bg-primary hover:bg-primary-hover text-white font-medium px-5 py-2.5 rounded-xl text-xs transition duration-150 flex-1">
                    Filtrar
                </button>
                @if($libraryFilter || $typeFilter)
                    <a href="{{ route('transactions.index') }}" class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700/80 font-medium px-4 py-2.5 rounded-xl text-xs transition duration-150 flex items-center justify-center">
                        Limpar
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Timeline List -->
    <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 shadow-xl">
        @if($transactions->isEmpty())
            <div class="text-center py-16">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 text-slate-700 mx-auto mb-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-.621-.504-1.125-1.125-1.125H9.75M10.5 6.75H21a.75.75 0 0 1 .75.75v10.5a.75.75 0 0 1-.75.75H10.5a.75.75 0 0 1-.75-.75V7.5a.75.75 0 0 1 .75-.75ZM3.75 6.75h3.75a.75.75 0 0 1 .75.75v10.5a.75.75 0 0 1-.75.75H3.75a.75.75 0 0 1-.75-.75V7.5a.75.75 0 0 1 .75-.75Z" />
                </svg>
                <h3 class="text-lg font-semibold text-slate-300">Nenhuma transação encontrada</h3>
                <p class="text-sm text-slate-500 mt-1">Nenhum evento registrado corresponde aos filtros aplicados.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-850 text-slate-400 text-xs uppercase tracking-wider font-bold">
                            <th class="py-4 px-4 w-32">Tipo</th>
                            <th class="py-4 px-4">Descrição</th>
                            <th class="py-4 px-4 w-48">Unidade</th>
                            <th class="py-4 px-4 w-40">Data / Hora</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-850/50 text-sm">
                        @foreach($transactions as $tx)
                            <tr class="hover:bg-slate-900/10 transition duration-150">
                                <!-- Badge -->
                                <td class="py-4 px-4">
                                    @if($tx->type === 'loan')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/15">
                                            Empréstimo
                                        </span>
                                    @elseif($tx->type === 'return')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/15">
                                            Devolução
                                        </span>
                                    @elseif($tx->type === 'overdue')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-500/10 text-red-400 border border-red-500/15 animate-pulse">
                                            Atraso
                                        </span>
                                    @elseif($tx->type === 'new_book')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-500/10 text-purple-400 border border-purple-500/15">
                                            Novo Livro
                                        </span>
                                    @elseif($tx->type === 'new_user')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/15">
                                            Novo Usuário
                                        </span>
                                    @endif
                                </td>

                                <!-- Description containing details page links -->
                                <td class="py-4 px-4 text-slate-200">
                                    @if($tx->type === 'loan' || $tx->type === 'return' || $tx->type === 'overdue')
                                        @if($tx->book_id && $tx->user_id)
                                            Prazo de devolução de '
                                            <a href="{{ route('catalog.show', $tx->book_id) }}" class="text-blue-400 hover:underline font-medium">{{ $tx->book_title }}</a>
                                            ' {{ $tx->type === 'loan' ? 'emprestado para o leitor' : ($tx->type === 'return' ? 'devolvido pelo leitor' : 'expirou para o leitor') }}
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
                                            Novo exemplar de '
                                            <a href="{{ route('catalog.show', $tx->book_id) }}" class="text-blue-400 hover:underline font-medium">{{ $tx->book_title }}</a>
                                            ' cadastrado na unidade {{ $tx->library_name }}.
                                        @else
                                            {{ $tx->description }}
                                        @endif
                                    @elseif($tx->type === 'new_user')
                                        @if($tx->user_id)
                                            Novo leitor 
                                            @if(Auth::user()->isManager())
                                                <a href="{{ route('users.edit', $tx->user_id) }}" class="text-blue-400 hover:underline font-medium">{{ $tx->user_name }}</a>
                                            @else
                                                <span class="font-medium text-slate-300">{{ $tx->user_name }}</span>
                                            @endif
                                            cadastrado no sistema.
                                        @else
                                            {{ $tx->description }}
                                        @endif
                                    @else
                                        {{ $tx->description }}
                                    @endif
                                </td>

                                <!-- Library context -->
                                <td class="py-4 px-4 text-xs text-slate-400">
                                    {{ $tx->library_name }}
                                </td>

                                <!-- Date -->
                                <td class="py-4 px-4 text-xs text-slate-400">
                                    {{ $tx->date->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
