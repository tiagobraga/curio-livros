@extends('layouts.app')

@section('title', __('Meus Empréstimos'))

@section('content')
<div class="space-y-8 max-w-5xl mx-auto">
    <!-- Header -->
    <div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-100">{{ __('Meus Empréstimos') }}</h1>
        <p class="text-sm text-slate-400 mt-1">{{ __('Acompanhe os livros emprestados e o histórico das suas leituras.') }}</p>
    </div>

    <!-- Table Container -->
    <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 shadow-xl space-y-4">
        @if($loans->isEmpty())
            <div class="text-center py-12">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-16 h-16 text-slate-700 mx-auto mb-3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
                <h3 class="text-base font-semibold text-slate-300">{{ __('Nenhum empréstimo registrado') }}</h3>
                <p class="text-xs text-slate-500 mt-1">{{ __('Você ainda não possui registros de empréstimos em nenhuma biblioteca.') }}</p>
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
                                <!-- Book Info -->
                                <td class="py-3 px-4">
                                    <a href="{{ route('catalog.show', $loan->localCopy->centralItem->id) }}" class="text-blue-400 hover:underline font-semibold">{{ $loan->localCopy->centralItem->title }}</a>
                                    <div class="text-[10px] text-slate-500">ISBN: {{ $loan->localCopy->centralItem->isbn ?? __('Sem ISBN') }}</div>
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
                                
                                <!-- Status Badge -->
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
@endsection
