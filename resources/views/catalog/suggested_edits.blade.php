@extends('layouts.app')

@section('title', __('Fila de Moderação de Metadados'))

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-300">{{ __('Fila de Moderação de Metadados') }}</h1>
        <p class="text-sm text-slate-500 mt-1">{{ __('Revise e aprove alterações sugeridas por gestores e atendentes nas obras do catálogo central.') }}</p>
    </div>

    @if($suggestions->isEmpty())
        <!-- Empty State -->
        <div class="text-center py-16 bg-white border border-brand-border rounded-3xl">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 text-slate-400 mx-auto mb-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
            </svg>
            <h3 class="text-lg font-semibold text-slate-300">{{ __('Tudo em ordem!') }}</h3>
            <p class="text-sm text-slate-500 mt-1">{{ __('Não há sugestões de edições aguardando aprovação no momento.') }}</p>
        </div>
    @else
        <!-- Suggestions List -->
        <div class="space-y-6">
            @foreach($suggestions as $sug)
                @php
                    $item = $sug->centralItem;
                    $currentMeta = $item->metadata;
                    $suggestedMeta = $sug->metadata;
                    
                    // Fields to compare
                    $fieldsToCompare = [
                        'title' => __('Título'),
                        'author' => __('Autor'),
                        'publisher' => __('Editora'),
                        'publication_year' => __('Ano de Publicação'),
                        'pages' => __('Páginas'),
                        'isbn' => __('ISBN'),
                        'subjects' => __('Assuntos / Tags'),
                        'description' => __('Sinopse / Resumo'),
                        'cover_url' => __('URL da Capa')
                    ];
                @endphp

                <!-- Curation Card -->
                <div class="bg-white border border-brand-border rounded-3xl p-6 md:p-8 shadow-xl space-y-6">
                    <!-- Top Info: Who suggested & Book Reference -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-brand-border/40 pb-4">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-accent">{{ __('Sugestão de Edição') }} #{{ $sug->id }}</span>
                            <h2 class="text-lg font-bold text-slate-200 mt-0.5">
                                {{ __('Obra original:') }} <a href="{{ route('catalog.show', $item->id) }}" class="text-primary hover:underline">{{ $item->title }}</a>
                            </h2>
                        </div>
                        <div class="text-left sm:text-right">
                            <span class="block text-xs text-slate-500">{{ __('Sugerido por:') }}</span>
                            <span class="text-sm font-semibold text-slate-300">{{ $sug->user->name }}</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-950 text-slate-400 border border-slate-800 ml-1.5 uppercase">
                                {{ $sug->user->role === 'manager' ? __('Gestor') : __('Atendente') }}
                            </span>
                        </div>
                    </div>

                    <!-- Dynamic Side-by-Side Diff Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="border-b border-brand-border/60 text-slate-500 text-xs uppercase font-bold">
                                    <th class="py-2 px-3 w-1/4">{{ __('Campo') }}</th>
                                    <th class="py-2 px-3 w-3/8 text-red-700 bg-red-50/45 rounded-t-lg">{{ __('Valor Atual') }}</th>
                                    <th class="py-2 px-3 w-3/8 text-emerald-800 bg-emerald-50/45 rounded-t-lg">{{ __('Valor Sugerido') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-brand-border/30">
                                @php $hasDiffs = false; @endphp
                                @foreach($fieldsToCompare as $field => $label)
                                    @php
                                        // Retrieve current value
                                        $currVal = $currentMeta[$field] ?? '';
                                        if (is_array($currVal)) $currVal = implode(', ', $currVal);
                                        
                                        // Retrieve suggested value
                                        $sugVal = $suggestedMeta[$field] ?? '';
                                        if (is_array($sugVal)) $sugVal = implode(', ', $sugVal);
                                        
                                        // Check if they are different
                                        $isDiff = trim($currVal) !== trim($sugVal);
                                        if ($isDiff) $hasDiffs = true;
                                    @endphp
                                    
                                    @if($isDiff)
                                        <tr class="hover:bg-brand-creme/30 transition">
                                            <td class="py-3 px-3 font-semibold text-slate-400">{{ $label }}</td>
                                            <td class="py-3 px-3 bg-red-50/20 text-slate-500 line-through">
                                                @if($field === 'cover_url' && $currVal)
                                                    <a href="{{ $currVal }}" target="_blank" class="underline text-xs break-all">{{ $currVal }}</a>
                                                @else
                                                    {{ $currVal ?: __('(Vazio)') }}
                                                @endif
                                            </td>
                                            <td class="py-3 px-3 bg-emerald-50/20 text-slate-200 font-medium">
                                                @if($field === 'cover_url' && $sugVal)
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-12 h-16 bg-slate-950 rounded overflow-hidden flex-shrink-0">
                                                            <img src="{{ $sugVal }}" class="w-full h-full object-cover">
                                                        </div>
                                                        <a href="{{ $sugVal }}" target="_blank" class="underline text-xs break-all">{{ $sugVal }}</a>
                                                    </div>
                                                @else
                                                    {{ $sugVal ?: __('(Vazio)') }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach

                                @if(!$hasDiffs)
                                    <tr>
                                        <td colspan="3" class="py-4 text-center text-slate-500 italic">
                                            {{ __('Nenhuma diferença de valores detectada entre o atual e o sugerido (possivelmente já atualizado).') }}
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Action Decisions -->
                    <div class="flex items-center justify-end gap-3 border-t border-brand-border/40 pt-4">
                        <!-- Reject form -->
                        <form action="{{ route('catalog.suggested_edits.reject', $sug->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-red-50 hover:bg-red-100 text-accent border border-red-200 font-semibold px-5 py-2 rounded-xl text-xs transition duration-150">
                                {{ __('Rejeitar Alteração') }}
                            </button>
                        </form>
                        
                        <!-- Approve form -->
                        <form action="{{ route('catalog.suggested_edits.approve', $sug->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-primary hover:bg-primary-hover text-white font-semibold px-6 py-2 rounded-xl text-xs transition duration-150 shadow-md">
                                {{ __('Aprovar e Mesclar') }}
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
