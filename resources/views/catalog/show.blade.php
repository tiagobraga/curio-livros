@extends('layouts.app')

@section('title', $dublinCore['title'])

@section('content')
<div class="space-y-8">
    <!-- Back link -->
    <div>
        <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-slate-200 transition duration-150">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Voltar ao catálogo
        </a>
    </div>

    <!-- Main Detail Section (Dublin Core & BIBFRAME) -->
    <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 md:p-8 shadow-xl flex flex-col md:flex-row gap-8">
        <!-- Cover Image -->
        <div class="w-full md:w-64 max-w-xs aspect-[3/4] bg-slate-950 rounded-2xl border border-slate-800/80 overflow-hidden flex items-center justify-center flex-shrink-0 mx-auto md:mx-0">
            @if($dublinCore['cover_url'])
                <img src="{{ $dublinCore['cover_url'] }}" alt="Capa de {{ $dublinCore['title'] }}" class="w-full h-full object-cover">
            @else
                <div class="text-slate-700 flex flex-col items-center justify-center p-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-16 h-16 mb-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                    </svg>
                    <span class="text-xs text-slate-500 uppercase tracking-wide">Sem Imagem</span>
                </div>
            @endif
        </div>

        <!-- Metadata Info -->
        <div class="flex-1 space-y-6">
            <div class="space-y-2">
                <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded bg-blue-500/10 text-blue-400 border border-blue-500/15">
                    {{ $item->item_type === 'book' ? 'Livro' : $item->item_type }}
                </span>
                <h1 class="text-3xl font-extrabold text-slate-100 tracking-tight">{{ $dublinCore['title'] }}</h1>
                <p class="text-lg text-slate-300">{{ $dublinCore['creator'] }}</p>
            </div>

            <!-- Metadata List (Dublin Core Mapped fields) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-y border-slate-850 py-5 text-sm">
                <div>
                    <span class="block text-xs text-slate-500 uppercase font-bold">Editora</span>
                    <span class="text-slate-200 mt-1 block">{{ $dublinCore['publisher'] }}</span>
                </div>
                <div>
                    <span class="block text-xs text-slate-500 uppercase font-bold">Ano de Publicação</span>
                    <span class="text-slate-200 mt-1 block">{{ $dublinCore['date'] }}</span>
                </div>
                <div>
                    <span class="block text-xs text-slate-500 uppercase font-bold">ISBN</span>
                    <span class="text-slate-200 mt-1 block">{{ $dublinCore['identifier'] }}</span>
                </div>
                <div>
                    <span class="block text-xs text-slate-500 uppercase font-bold">Extensão (Páginas)</span>
                    <span class="text-slate-200 mt-1 block">{{ $dublinCore['pages'] }}</span>
                </div>
            </div>

            <!-- Subjects / Tags -->
            @if($dublinCore['subject'])
                <div class="space-y-1.5">
                    <span class="block text-xs text-slate-500 uppercase font-bold">Assuntos / Tags</span>
                    <div class="flex flex-wrap gap-2 pt-1">
                        @foreach(explode(', ', $dublinCore['subject']) as $tag)
                            <span class="text-xs bg-slate-800/60 text-slate-300 border border-slate-700/50 px-3 py-1 rounded-xl">
                                {{ $tag }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Description / Summary -->
            <div class="space-y-1.5">
                <span class="block text-xs text-slate-500 uppercase font-bold">Sinopse / Resumo</span>
                <p class="text-slate-400 text-sm leading-relaxed">{{ $dublinCore['description'] }}</p>
            </div>
            
            <!-- Linked Data Schema Badge -->
            <div class="text-[10px] text-slate-600 flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-.778.099-1.533.284-2.253m0 0A17.919 17.919 0 0 0 12 7.5a17.919 17.919 0 0 0 8.716 2.253" />
                </svg>
                <span>Modelo semântico estruturado conforme especificações do BIBFRAME (Linked Data) e serializado em JSON-LD.</span>
            </div>
        </div>
    </div>

    <!-- Physical Copies section (BIBFRAME Item) -->
    <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 shadow-xl">
        <h2 class="text-xl font-bold tracking-tight text-slate-100 mb-6">Exemplares nas Unidades</h2>
        
        @if($copies->isEmpty())
            <div class="text-center py-8 border border-dashed border-slate-800 rounded-2xl">
                <p class="text-slate-500 text-sm">Não há exemplares físicos cadastrados para esta obra nas unidades de biblioteca.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-850 text-slate-400 text-xs uppercase tracking-wider font-bold">
                            <th class="py-4 px-4">Biblioteca / Unidade</th>
                            <th class="py-4 px-4">Número de Chamada</th>
                            <th class="py-4 px-4">Localização (Estante)</th>
                            <th class="py-4 px-4">Total</th>
                            <th class="py-4 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-850/50 text-sm">
                        @foreach($copies as $copy)
                            <tr class="hover:bg-slate-900/10 transition duration-150">
                                <td class="py-4 px-4 font-semibold text-slate-200">
                                    {{ $copy->library->name }}
                                    @if(Auth::user()->library_id === $copy->library_id)
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-blue-500/10 text-blue-400 border border-blue-500/15">
                                            Sua Unidade
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-slate-300 font-mono text-xs">
                                    {{ $copy->call_number ?: 'Não informado' }}
                                </td>
                                <td class="py-4 px-4 text-slate-400">
                                    {{ $copy->shelf_location ?: 'Não informado' }}
                                </td>
                                <td class="py-4 px-4 text-slate-300">
                                    {{ $copy->quantity }}
                                </td>
                                <td class="py-4 px-4">
                                    @if($copy->status === 'available')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/15">
                                            Disponível
                                        </span>
                                    @elseif($copy->status === 'borrowed')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/15">
                                            Emprestado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-500/10 text-slate-400 border border-slate-500/15">
                                            {{ $copy->status }}
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
