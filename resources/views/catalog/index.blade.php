@extends('layouts.app')

@section('title', __('Catálogo Central'))

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-100">{{ __('Catálogo Central') }}</h1>
            <p class="text-sm text-slate-400 mt-1">{{ __('Busque obras catalogadas ou adicione novos exemplares.') }}</p>
        </div>
        
        @if(Auth::user()->isAttendant() || Auth::user()->isCurator())
            <a href="{{ route('catalog.create') }}" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white font-medium px-4 py-2.5 rounded-xl text-sm transition duration-150 shadow-lg shadow-emerald-950/15">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.75-7.75h-15" />
                </svg>
                {{ __('Adicionar Livro (Scanner)') }}
            </a>
        @endif
    </div>

    <!-- Search and Filter Form -->
    <form action="{{ route('catalog.index') }}" method="GET" class="space-y-4 max-w-4xl">
        <input type="hidden" name="sort" id="hidden-sort" value="{{ $sort }}">
        <input type="hidden" name="direction" id="hidden-direction" value="{{ $direction }}">
        <input type="hidden" name="layout" id="hidden-layout" value="{{ $layout }}">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="absolute left-4 top-3.5 w-5 h-5 text-slate-500">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input type="text" name="search" value="{{ $search }}"
                    placeholder="{{ __('Pesquisar por título, autor ou ISBN...') }}" 
                    class="w-full bg-slate-900/40 border border-slate-800 rounded-xl pl-12 pr-4 py-3 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 transition duration-200">
            </div>
            
            <div class="flex gap-2 w-full sm:w-auto">
                <button type="button" id="btn-toggle-filters" 
                    class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 font-medium px-4 py-3 rounded-xl text-sm transition duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-emerald-700">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                    </svg>
                    <span>{{ __('Filtros') }}</span>
                </button>
                <button type="submit" class="flex-1 sm:flex-initial bg-primary hover:bg-primary-hover text-white font-medium px-6 py-3 rounded-xl text-sm transition duration-150">
                    {{ __('Buscar') }}
                </button>
                @if($search || $libraryFilter || $authorFilter || $subjectFilter || $periodFilter || $availableFilter || $sort !== 'title_asc')
                    <a href="{{ route('catalog.index') }}" class="bg-slate-900 hover:bg-slate-800 text-slate-400 border border-slate-800 font-medium px-4 py-3 rounded-xl text-sm transition duration-150 flex items-center justify-center" title="{{ __('Limpar busca e filtros') }}">
                        {{ __('Limpar') }}
                    </a>
                @endif
            </div>
        </div>

        <!-- Advanced Filters Panel -->
        <div id="advanced-filters-panel" class="hidden p-5 bg-slate-900/20 border border-slate-850 rounded-2xl space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                
                <!-- Library Filter -->
                <div class="space-y-1.5">
                    <label for="library_id" class="block text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('Biblioteca / Unidade') }}</label>
                    <select id="library_id" name="library_id" 
                        class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150 cursor-pointer">
                        <option value="">{{ __('Todas as Bibliotecas') }}</option>
                        @foreach($libraries as $lib)
                            <option value="{{ $lib->id }}" {{ $libraryFilter == $lib->id ? 'selected' : '' }}>
                                {{ $lib->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Subject / Genre Filter -->
                <div class="space-y-1.5">
                    <label for="subject" class="block text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('Assunto / Gênero') }}</label>
                    <select id="subject" name="subject" 
                        class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150 cursor-pointer">
                        <option value="">{{ __('Todos os Assuntos') }}</option>
                        @foreach($uniqueSubjects as $subj)
                            <option value="{{ $subj }}" {{ $subjectFilter === $subj ? 'selected' : '' }}>
                                {{ $subj }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Author Filter -->
                <div class="space-y-1.5">
                    <label for="author" class="block text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('Autor específico') }}</label>
                    <input type="text" id="author" name="author" value="{{ $authorFilter }}" placeholder="{{ __('ex: Machado de Assis') }}"
                        class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                </div>

                <!-- Period Filter -->
                <div class="space-y-1.5">
                    <label for="period" class="block text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('Data de Cadastro') }}</label>
                    <select id="period" name="period" 
                        class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150 cursor-pointer">
                        <option value="">{{ __('Qualquer data') }}</option>
                        <option value="30_days" {{ $periodFilter === '30_days' ? 'selected' : '' }}>{{ __('Recentes (Últimos 30 dias)') }}</option>
                        <option value="90_days" {{ $periodFilter === '90_days' ? 'selected' : '' }}>{{ __('Últimos 90 dias') }}</option>
                    </select>
                </div>

                <!-- Availability Checkbox -->
                <div class="flex items-center h-full pt-5">
                    <label class="flex items-center gap-3 text-xs font-semibold text-slate-300 hover:text-primary cursor-pointer select-none">
                        <input type="checkbox" name="available" value="1" {{ $availableFilter ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-slate-800 bg-slate-950 text-emerald-600 focus:ring-emerald-500 focus:ring-offset-slate-950">
                        <span>{{ __('Apenas Disponíveis para Empréstimo') }}</span>
                    </label>
                </div>
            </div>
            
            <div class="flex justify-end gap-2 pt-2">
                <button type="submit" class="bg-primary/20 hover:bg-primary/30 text-emerald-400 border border-emerald-800/50 px-4 py-2 rounded-lg text-xs font-bold transition duration-150">
                    {{ __('Aplicar Filtros') }}
                </button>
            </div>
        </div>
    </form>

    <!-- Results Section -->
    @if($items->isEmpty())
        <div class="bg-slate-900 border border-slate-800/80 rounded-2xl p-12 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-800 text-slate-500 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <h3 class="text-base font-semibold text-white mb-1">{{ __('Nenhum livro encontrado') }}</h3>
            <p class="text-xs text-slate-400 max-w-sm mx-auto">
                {{ __('Tente ajustar seus termos de busca ou filtros para encontrar o que procura no catálogo.') }}
            </p>
        </div>
    @else
        <div class="flex items-center justify-between gap-4 mb-4 flex-wrap">
            <p class="text-xs text-slate-400">
                {{ __('Mostrando') }} <span class="font-medium text-slate-200">{{ $items->firstItem() ?? 0 }}</span> {{ __('a') }} <span class="font-medium text-slate-200">{{ $items->lastItem() ?? 0 }}</span> {{ __('de') }} <span class="font-medium text-slate-200">{{ $items->total() }}</span> {{ __('resultados') }}
            </p>

            <div class="flex items-center gap-3">
                <!-- Sorting select -->
                <div class="flex items-center gap-2">
                    <label for="sort-select" class="text-xs text-slate-400 hidden sm:inline">{{ __('Ordenar por:') }}</label>
                    <select id="sort-select" onchange="updateSorting(this.value)"
                        class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-1.5 text-xs text-slate-300 focus:outline-none focus:border-emerald-500 cursor-pointer">
                        <option value="created_at_desc" {{ $sort === 'created_at' && $direction === 'desc' ? 'selected' : '' }}>{{ __('Mais Recentes') }}</option>
                        <option value="title_asc" {{ $sort === 'title' && $direction === 'asc' ? 'selected' : '' }}>{{ __('Título (A-Z)') }}</option>
                        <option value="title_desc" {{ $sort === 'title' && $direction === 'desc' ? 'selected' : '' }}>{{ __('Título (Z-A)') }}</option>
                        <option value="author_asc" {{ $sort === 'author' && $direction === 'asc' ? 'selected' : '' }}>{{ __('Autor (A-Z)') }}</option>
                        <option value="publisher_asc" {{ $sort === 'publisher' && $direction === 'asc' ? 'selected' : '' }}>{{ __('Editora (A-Z)') }}</option>
                        <option value="publisher_desc" {{ $sort === 'publisher' && $direction === 'desc' ? 'selected' : '' }}>{{ __('Editora (Z-A)') }}</option>
                        <option value="year_asc" {{ $sort === 'year' && $direction === 'asc' ? 'selected' : '' }}>{{ __('Ano (Mais Antigo)') }}</option>
                        <option value="year_desc" {{ $sort === 'year' && $direction === 'desc' ? 'selected' : '' }}>{{ __('Ano (Mais Recente)') }}</option>
                    </select>
                </div>

                <!-- Layout Toggles -->
                <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-xl border border-slate-850">
                    <button type="button" onclick="updateLayout('card')" 
                        class="p-1.5 rounded-lg transition {{ $layout === 'card' ? 'bg-primary text-white shadow' : 'text-slate-500 hover:text-slate-300' }}" title="{{ __('Visualização em Cards') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25A2.25 2.25 0 0 1 13.5 8.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                        </svg>
                    </button>
                    <button type="button" onclick="updateLayout('list')" 
                        class="p-1.5 rounded-lg transition {{ $layout === 'list' ? 'bg-primary text-white shadow' : 'text-slate-500 hover:text-slate-300' }}" title="{{ __('Visualização em Lista') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375 0 1 1-.75 0 .375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375 0 1 1-.75 0 .375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375 0 1 1-.75 0 .375 0 0 1 .75 0Z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        @if($layout === 'list')
            <!-- Layout em Lista -->
                @foreach($items as $item)
                    @php
                        $meta = $item->metadata;
                        $author = $meta['author'] ?? __('Autor desconhecido');
                        $publisher = $meta['publisher'] ?? __('Editora desconhecida');
                        $year = $meta['publication_year'] ?? '';
                        $cover = $meta['cover_url'] ?? null;
                        $tags = $meta['subjects'] ?? [];
                    @endphp
                    <a href="{{ route('catalog.show', $item->id) }}" class="group p-4 bg-slate-900/30 hover:bg-slate-900/50 backdrop-blur border border-slate-800/80 rounded-2xl flex items-center justify-between gap-4 hover:border-slate-700 transition duration-200 block">
                        <div class="flex items-center gap-4 flex-1 min-w-0">
                            <!-- Cover miniatura -->
                            <div class="w-12 h-16 bg-slate-950 rounded-lg overflow-hidden border border-slate-800 flex-shrink-0 flex items-center justify-center">
                                @if($cover)
                                    <img src="{{ $cover }}" alt="Capa" class="w-full h-full object-cover">
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-slate-700">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                    </svg>
                                @endif
                            </div>

                            <!-- Info Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 flex-1 min-w-0">
                                <!-- Title & Author -->
                                <div class="space-y-0.5 min-w-0">
                                    <h4 class="font-bold text-sm text-slate-100 group-hover:text-primary transition duration-150 truncate">
                                        {{ $item->title }}
                                    </h4>
                                    <p class="text-xs text-slate-400 font-medium truncate">{{ $author }}</p>
                                </div>

                                <!-- Publisher & Year -->
                                <div class="text-xs text-slate-400 flex flex-col justify-center truncate">
                                    <span class="font-semibold text-slate-350 truncate">{{ $publisher }}</span>
                                    @if($year)
                                        <span class="text-slate-500">{{ __('Ano:') }} {{ $year }}</span>
                                    @endif
                                </div>

                                <!-- Subjects / Tags -->
                                <div class="flex items-center gap-1.5 flex-wrap overflow-hidden">
                                    @foreach(array_slice($tags, 0, 3) as $tag)
                                        <span class="text-[10px] font-medium bg-slate-800/40 text-slate-450 border border-slate-800/80 px-2 py-0.5 rounded-md whitespace-nowrap">
                                            {{ $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Action -->
                        <div class="flex-shrink-0">
                            <span class="inline-block bg-slate-900/60 group-hover:bg-slate-800 text-slate-300 border border-slate-800 font-bold px-4 py-2 rounded-xl text-xs transition duration-150">
                                {{ __('Detalhes') }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <!-- Layout em Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mt-6">
                @foreach($items as $item)
                    @php
                        $meta = $item->metadata;
                        $author = $meta['author'] ?? __('Autor desconhecido');
                        $publisher = $meta['publisher'] ?? __('Editora desconhecida');
                        $year = $meta['publication_year'] ?? '';
                        $cover = $meta['cover_url'] ?? null;
                        $tags = $meta['subjects'] ?? [];
                    @endphp
                    <a href="{{ route('catalog.show', $item->id) }}" class="group bg-slate-900/40 hover:bg-slate-900/60 backdrop-blur border border-slate-800/80 hover:border-slate-700/80 rounded-2xl overflow-hidden flex flex-col shadow-lg transition duration-200 block">
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
                                {{ $item->item_type === 'book' ? __('Livro') : $item->item_type }}
                            </span>
                        </div>

                        <!-- Book info -->
                        <div class="p-5 flex-1 flex flex-col justify-between">
                            <div class="space-y-1.5">
                                <h3 class="font-bold text-slate-100 group-hover:text-primary transition duration-150 line-clamp-2">
                                    {{ $item->title }}
                                </h3>
                                <p class="text-xs text-slate-400 truncate">{{ $author }}</p>
                                <p class="text-[10px] text-slate-500 mb-2">
                                    {{ $publisher }} {{ $year ? "• {$year}" : "" }}
                                </p>
                                
                                <!-- Discrete "Mais detalhes" link -->
                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-accent group-hover:text-accent-hover transition duration-150">
                                    {{ __('Mais detalhes') }} &rarr;
                                </span>
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
                    </a>
                @endforeach
            </div>
        @endif

        <!-- Pagination -->
        <div class="mt-8">
            {{ $items->links() }}
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('btn-toggle-filters');
        const filtersPanel = document.getElementById('advanced-filters-panel');
        if (toggleBtn && filtersPanel) {
            toggleBtn.addEventListener('click', function() {
                filtersPanel.classList.toggle('hidden');
            });
        }
    });

    function updateSorting(val) {
        const parts = val.split('_');
        document.getElementById('hidden-sort').value = parts[0];
        document.getElementById('hidden-direction').value = parts[1];
        document.getElementById('hidden-sort').form.submit();
    }

    function updateLayout(layoutVal) {
        document.getElementById('hidden-layout').value = layoutVal;
        document.getElementById('hidden-layout').form.submit();
    }
</script>
@endsection
