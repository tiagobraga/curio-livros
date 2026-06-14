@extends('layouts.app')

@section('title', 'Catálogo Central')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-100">Catálogo Central</h1>
            <p class="text-sm text-slate-400 mt-1">Busque obras catalogadas ou adicione novos exemplares.</p>
        </div>
        
        @if(Auth::user()->isAttendant())
            <a href="{{ route('catalog.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white font-medium px-4 py-2.5 rounded-xl text-sm transition duration-150 shadow-lg shadow-blue-500/15">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.75-7.75h-15" />
                </svg>
                Adicionar Livro (Scanner)
            </a>
        @endif
    </div>

    <!-- Search Form -->
    <form action="{{ route('catalog.index') }}" method="GET" class="flex gap-3 max-w-2xl">
        <div class="relative flex-1">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="absolute left-4 top-3.5 w-5 h-5 text-slate-500">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input type="text" name="search" value="{{ $search }}"
                placeholder="Pesquisar por título, autor ou ISBN..." 
                class="w-full bg-slate-900/40 border border-slate-800 rounded-xl pl-12 pr-4 py-3 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-200">
        </div>
        <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700/80 font-medium px-6 py-3 rounded-xl text-sm transition duration-150">
            Buscar
        </button>
        @if($search)
            <a href="{{ route('catalog.index') }}" class="bg-slate-900 hover:bg-slate-800 text-slate-400 border border-slate-800 font-medium px-4 py-3 rounded-xl text-sm transition duration-150 flex items-center justify-center" title="Limpar busca">
                Limpar
            </a>
        @endif
    </form>

    <!-- Catalog Grid -->
    @if($items->isEmpty())
        <div class="text-center py-16 bg-slate-900/20 border border-slate-850 rounded-3xl">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-16 h-16 text-slate-700 mx-auto mb-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
            </svg>
            <h3 class="text-lg font-semibold text-slate-300">Nenhum livro localizado</h3>
            <p class="text-sm text-slate-500 mt-1">Não encontramos nenhuma obra correspondente aos critérios no catálogo.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($items as $item)
                @php
                    $meta = $item->metadata;
                    $author = $meta['author'] ?? 'Autor desconhecido';
                    $publisher = $meta['publisher'] ?? 'Editora desconhecida';
                    $year = $meta['publication_year'] ?? '';
                    $cover = $meta['cover_url'] ?? null;
                    $tags = $meta['subjects'] ?? [];
                @endphp
                <div class="group bg-slate-900/40 hover:bg-slate-900/60 backdrop-blur border border-slate-800/80 hover:border-slate-700/80 rounded-2xl overflow-hidden flex flex-col shadow-lg transition duration-200">
                    <!-- Cover image container -->
                    <div class="relative aspect-[3/4] bg-slate-950 flex items-center justify-center overflow-hidden border-b border-slate-850">
                        @if($cover)
                            <img src="{{ $cover }}" alt="Capa de {{ $item->title }}" class="w-full h-full object-cover transition duration-300 group-hover:scale-105" loading="lazy">
                        @else
                            <!-- Beautiful placeholder book -->
                            <div class="flex flex-col items-center justify-center p-4 text-center text-slate-700">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-12 h-12 mb-2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                </svg>
                                <span class="text-xs font-semibold px-2 uppercase tracking-wide truncate max-w-[150px]">{{ $item->title }}</span>
                            </div>
                        @endif
                        
                        <!-- Badges/Item type -->
                        <span class="absolute top-3 right-3 text-[10px] uppercase font-bold px-2 py-0.5 rounded-full bg-slate-950/80 border border-slate-800 text-slate-400">
                            {{ $item->item_type === 'book' ? 'Livro' : $item->item_type }}
                        </span>
                    </div>

                    <!-- Book info -->
                    <div class="p-5 flex-1 flex flex-col">
                        <div class="flex-1 space-y-1.5">
                            <h3 class="font-bold text-slate-100 group-hover:text-blue-400 transition duration-150 line-clamp-2">
                                <a href="{{ route('catalog.show', $item->id) }}">{{ $item->title }}</a>
                            </h3>
                            <p class="text-xs text-slate-400 truncate">{{ $author }}</p>
                            <p class="text-[10px] text-slate-500">
                                {{ $publisher }} {{ $year ? "• {$year}" : "" }}
                            </p>
                        </div>

                        <!-- Subjects / Tags -->
                        @if(!empty($tags))
                            <div class="mt-4 flex flex-wrap gap-1.5 line-clamp-1 h-6 overflow-hidden">
                                @foreach(array_slice($tags, 0, 2) as $tag)
                                    <span class="text-[10px] font-medium bg-slate-800/40 text-slate-400 border border-slate-800 px-2 py-0.5 rounded-md">
                                        {{ $tag }}
                                    </span>
                                @endforeach
                                @if(count($tags) > 2)
                                    <span class="text-[10px] font-medium text-slate-500 px-1 py-0.5">
                                        +{{ count($tags) - 2 }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $items->links() }}
        </div>
    @endif
</div>
@endsection
