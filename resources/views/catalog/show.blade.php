@extends(isset($isShared) && $isShared ? 'layouts.shared' : 'layouts.app')

@section('title', $dublinCore['title'])

@section('head')
    @php
        $synopsis = str_replace(["\r", "\n"], [" ", " "], trim(strip_tags($dublinCore['description'] ?? '')));
        $synopsis = preg_replace('/\s+/', ' ', $synopsis);
    @endphp
    <!-- Zotero Metadata (Dublin Core) -->
    <meta name="DC.title" content="{{ $item->title }}" />
    <meta name="DC.creator" content="{{ $dublinCore['creator'] }}" />
    <meta name="DC.publisher" content="{{ $dublinCore['publisher'] }}" />
    <meta name="DC.date" content="{{ $dublinCore['date'] }}" />
    @if($item->isbn)
        <meta name="DC.identifier" content="urn:isbn:{{ $item->isbn }}" />
    @endif
    <meta name="DC.type" content="Book" />
    <meta name="DC.format" content="print" />
    <meta name="DC.language" content="pt-BR" />
    @if($synopsis)
        <meta name="DC.description" content="{{ $synopsis }}" />
    @endif

    <!-- Standard & Lowercase Alternate Metadata for Zotero/Crawlers (guarantees Abstract/Resumo mapping) -->
    <meta name="description" content="{{ $synopsis }}" />
    <meta name="author" content="{{ $dublinCore['creator'] }}" />
    <meta name="dc.description" content="{{ $synopsis }}" />
    <meta name="dc.creator" content="{{ $dublinCore['creator'] }}" />
    <meta name="dc.publisher" content="{{ $dublinCore['publisher'] }}" />
    <meta name="dc.date" content="{{ $dublinCore['date'] }}" />
    <meta name="dcterms.description" content="{{ $synopsis }}" />
    <meta name="dcterms.abstract" content="{{ $synopsis }}" />

    <!-- Google Scholar / Highwire Press Meta Tags -->
    <meta name="citation_title" content="{{ $item->title }}" />
    <meta name="citation_author" content="{{ $dublinCore['creator'] }}" />
    <meta name="citation_publisher" content="{{ $dublinCore['publisher'] }}" />
    <meta name="citation_publication_date" content="{{ $dublinCore['date'] }}" />
    @if($item->isbn)
        <meta name="citation_isbn" content="{{ $item->isbn }}" />
    @endif
    @if($synopsis)
        <meta name="citation_abstract" content="{{ $synopsis }}" />
    @endif

    @php
        // Detect actual host and scheme from request (bypasses Docker localhost APP_URL limitation)
        $host = request()->getHost();
        $proto = request()->header('X-Forwarded-Proto');
        if (empty($proto)) {
            $proto = request()->isSecure() ? 'https' : 'http';
        }
        if ($host !== 'localhost' && $host !== '127.0.0.1') {
            $proto = 'https';
        }

        // Build the absolute short URL
        $ogUrl = $proto . '://' . $host . '/' . $item->share_code;

        // Build the absolute cover image URL
        $ogImage = null;
        if (!empty($dublinCore['cover_url'])) {
            $coverUrl = $dublinCore['cover_url'];
            if (str_starts_with($coverUrl, 'http://') || str_starts_with($coverUrl, 'https://')) {
                $ogImage = $coverUrl;
            } else {
                $cleanPath = ltrim($coverUrl, '/');
                $ogImage = $proto . '://' . $host . '/' . $cleanPath;
            }
            // Promote to HTTPS in production
            if ($host !== 'localhost' && $host !== '127.0.0.1' && str_starts_with($ogImage, 'http://')) {
                $ogImage = str_replace('http://', 'https://', $ogImage);
            }
        }
        
        $ogDescription = !empty($dublinCore['creator']) && $dublinCore['creator'] !== 'Autor desconhecido'
            ? "Obra por " . $dublinCore['creator'] . ". " . Str::limit(strip_tags($dublinCore['description'] ?? ''), 150)
            : Str::limit(strip_tags($dublinCore['description'] ?? ''), 150);
    @endphp

    <!-- Canonical link (forces Zotero to save the short URL as the main source and snapshot target) -->
    <link rel="canonical" href="{{ $ogUrl }}" />

    <!-- Open Graph (Facebook, WhatsApp, Slack, etc.) -->
    <meta property="og:site_name" content="Biblió" />
    <meta property="og:title" content="{{ $item->title }}" />
    <meta property="og:description" content="{{ $ogDescription }}" />
    <meta property="og:type" content="book" />
    <meta property="og:url" content="{{ $ogUrl }}" />
    @if($ogImage)
        <meta property="og:image" content="{{ $ogImage }}" />
    @endif

    <!-- Twitter Card (Twitter/X) -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $item->title }}" />
    <meta name="twitter:description" content="{{ $ogDescription }}" />
    @if($ogImage)
        <meta name="twitter:image" content="{{ $ogImage }}" />
    @endif
@endsection

@section('content')
<div class="space-y-8">
    <!-- Back link -->
    @if(!request()->routeIs('catalog.shared'))
        <div>
            <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-slate-200 transition duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Voltar ao catálogo
            </a>
        </div>
    @endif

    <!-- Main Detail Section (Dublin Core & BIBFRAME) -->
    <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 md:p-8 shadow-xl space-y-8">
        <!-- ROW 1: Cover and Metadata details -->
        <div class="flex flex-col md:flex-row gap-8 items-start">
            <!-- Cover Image -->
            <div class="w-full md:w-64 max-w-xs aspect-[3/4] bg-slate-950 rounded-2xl border border-slate-800/80 overflow-hidden flex items-center justify-center flex-shrink-0 mx-auto md:mx-0 relative">
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
                <div class="flex justify-between items-start gap-4 flex-wrap sm:flex-nowrap">
                    <div class="space-y-2">
                        <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-800 border border-emerald-500/20">
                            {{ $item->item_type === 'book' ? 'Livro' : $item->item_type }}
                        </span>
                        <h1 class="text-3xl font-extrabold text-slate-100 tracking-tight">{{ $dublinCore['title'] }}</h1>
                        
                        <!-- Author & Rating (Vertical structure) -->
                        <div class="space-y-1.5">
                            <p class="text-lg text-slate-305 font-semibold">{{ $dublinCore['creator'] }}</p>
                            
                            <!-- General Rating below author name -->
                            <div class="flex items-center gap-2 text-xs text-slate-405 font-medium flex-wrap">
                                @if($reviewsCount > 0)
                                    <div class="flex items-center gap-1 bg-amber-500/10 text-amber-400 border border-amber-500/15 px-2 py-0.5 rounded-lg text-xs font-bold">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5">
                                            <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd" />
                                        </svg>
                                        <span>{{ number_format($avgRating, 1) }}</span>
                                    </div>
                                    <span class="text-slate-700">•</span>
                                    <div class="flex text-amber-400/80">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5 {{ $i <= round($avgRating ?? 0) ? 'text-amber-400' : 'text-slate-750' }}">
                                                <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd" />
                                            </svg>
                                        @endfor
                                    </div>
                                    <span class="text-[10px] text-slate-500 font-bold">({{ $reviewsCount }} {{ $reviewsCount == 1 ? 'avaliação' : 'avaliações' }})</span>
                                @else
                                    <span class="text-slate-500 italic text-[11px]">Nenhuma avaliação ainda</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 flex-shrink-0">
                        <button onclick="openShareModal()" class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 px-4 py-2.5 rounded-xl text-xs font-semibold transition duration-150 shadow-sm flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-slate-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186.003-.004c.007-.008.016-.017.027-.027m-.03.031a2.25 2.25 0 0 1 2.244-2.077H17.25a2.25 2.25 0 1 1-2.244 2.077H9.461m.827-1.977a2.25 2.25 0 1 0-2.243-2.077m0 2.077.001.002.002.003m-2.244-2.078c.07.03.14.062.21.095m0 0A2.25 2.25 0 1 1 5.922 14.1c-.07-.033-.14-.065-.21-.095m0 0a2.25 2.25 0 1 0 2.244 2.077H17.25a2.25 2.25 0 1 0-2.244-2.077H9.461" />
                            </svg>
                            Compartilhar
                        </button>
                        @if(Auth::user()?->isAttendant() || Auth::user()?->isCurator())
                            <a href="{{ route('catalog.edit', $item->id) }}" class="bg-primary hover:bg-primary-hover text-white px-4 py-2.5 rounded-xl text-xs font-semibold transition duration-150 shadow-md">
                                {{ Auth::user()?->isCurator() ? 'Editar Livro' : 'Sugerir Alteração' }}
                            </a>
                        @endif
                    </div>
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
                    @php
                        $allTags = explode(', ', $dublinCore['subject']);
                        $hasManyTags = count($allTags) > 10;
                    @endphp
                    <div class="space-y-1.5">
                        <span class="block text-xs text-slate-500 uppercase font-bold">Assuntos / Tags</span>
                        <div class="flex flex-wrap gap-2 pt-1">
                            @foreach($allTags as $index => $tag)
                                <span class="text-xs bg-slate-800/60 text-slate-300 border border-slate-700/50 px-3 py-1 rounded-xl {{ $index >= 10 ? 'hidden extra-tag' : '' }}">
                                    {{ $tag }}
                                </span>
                            @endforeach
                            
                            @if($hasManyTags)
                                <button type="button" id="btn-toggle-tags" class="text-[10px] bg-slate-800/20 hover:bg-slate-800/40 text-blue-400 border border-blue-900/30 px-3 py-1 rounded-xl font-bold transition">
                                    Ver mais (+{{ count($allTags) - 10 }})
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- ROW 2: Synopsis (Full width below Row 1) -->
        <div class="pt-6 border-t border-slate-850 space-y-3">
            <span class="block text-xs text-slate-500 uppercase font-bold">Sinopse / Resumo</span>
            <div id="synopsis-wrapper" class="text-slate-300 text-sm leading-relaxed">
                @if(strlen(strip_tags($dublinCore['description'])) > 1000)
                    <span id="synopsis-truncated">{!! mb_substr(strip_tags($dublinCore['description']), 0, 1000) !!}...</span>
                    <span id="synopsis-full" class="hidden">{!! $dublinCore['description'] !!}</span>
                    <button type="button" id="btn-toggle-synopsis" class="text-blue-400 hover:text-blue-300 font-semibold block mt-2 focus:outline-none text-xs">
                        Mostrar mais
                    </button>
                @else
                    {!! $dublinCore['description'] !!}
                @endif
            </div>
        </div>
    </div>

    @if(!request()->routeIs('catalog.shared') || (isset($targetLibrary) && $targetLibrary))
    <!-- Physical Copies section (BIBFRAME Item) -->
    <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 shadow-xl">
        <h2 class="text-xl font-bold tracking-tight text-slate-100 mb-6">
            @if(isset($targetLibrary) && $targetLibrary)
                {{ __('Exemplares Disponíveis —') }} {{ $targetLibrary->name }}
            @elseif(Auth::user() && !Auth::user()->isAdmin() && !Auth::user()->isCurator())
                {{ __('Exemplares Disponíveis nas Suas Unidades') }}
            @else
                {{ __('Exemplares nas Unidades') }}
            @endif
        </h2>
        
        @if($copies->isEmpty())
            <div class="text-center py-8 border border-dashed border-slate-800 rounded-2xl">
                <p class="text-slate-500 text-sm">
                    @if(isset($targetLibrary) && $targetLibrary)
                        {{ __('Não há exemplares físicos disponíveis para esta obra nesta biblioteca.') }}
                    @elseif(Auth::user() && !Auth::user()->isAdmin() && !Auth::user()->isCurator())
                        {{ __('Não há exemplares físicos disponíveis para esta obra nas bibliotecas com as quais você possui vínculo.') }}
                    @else
                        {{ __('Não há exemplares físicos cadastrados para esta obra nas unidades de biblioteca.') }}
                    @endif
                </p>
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
                                    @if(Auth::user() && Auth::user()->libraries->contains($copy->library_id))
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
                                    @elseif($copy->status === 'unavailable')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/15">
                                            Indisponível
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-500/10 text-slate-400 border border-slate-500/15">
                                            {{ ucfirst($copy->status) }}
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
    @endif

    @if(Auth::user()?->isAttendant() || Auth::user()?->isCurator())
        <!-- Book Circulation History (Visible only to staff) -->
        <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 shadow-xl space-y-4">
            <div>
                <h3 class="text-lg font-bold text-slate-100">Histórico de Empréstimos</h3>
                <p class="text-xs text-slate-400 mt-0.5">Últimos empréstimos realizados deste livro.</p>
            </div>

            @if($loans->isEmpty())
                <div class="text-center py-8">
                    <p class="text-slate-500 text-xs italic">Nenhum empréstimo registrado para esta obra.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-850 text-slate-400 text-xs uppercase tracking-wider font-bold">
                                <th class="py-3 px-4">Leitor</th>
                                <th class="py-3 px-4">Unidade</th>
                                <th class="py-3 px-4">Data Empréstimo</th>
                                <th class="py-3 px-4">Data Limite</th>
                                <th class="py-3 px-4">Data Devolução</th>
                                <th class="py-3 px-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-850/50 text-sm">
                            @foreach($loans as $loan)
                                <tr class="hover:bg-slate-900/10 transition duration-150">
                                    <!-- Reader Info -->
                                    <td class="py-3 px-4">
                                        @if(Auth::user()?->isManager())
                                            <a href="{{ route('users.edit', $loan->user->id) }}" class="text-blue-400 hover:underline font-semibold">{{ $loan->user->name }}</a>
                                        @else
                                            <span class="text-slate-200 font-medium">{{ $loan->user->name }}</span>
                                        @endif
                                        <div class="text-[10px] text-slate-500">{{ $loan->user->email }}</div>
                                    </td>
                                    
                                    <!-- Library -->
                                    <td class="py-3 px-4 text-slate-300 text-xs">
                                        {{ $loan->localCopy->library->name }}
                                    </td>
                                    
                                    <!-- Dates -->
                                    <td class="py-3 px-4 text-slate-300 text-xs">
                                        {{ $loan->loan_date->format('d/m/Y') }}
                                        <span class="block text-[10px] text-slate-500">por {{ $loan->attendant->name }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-xs font-medium {{ (!$loan->return_date && $loan->due_date->isPast()) ? 'text-red-400' : 'text-slate-300' }}">
                                        {{ $loan->due_date->format('d/m/Y') }}
                                    </td>
                                    <td class="py-3 px-4 text-slate-400 text-xs">
                                        {{ $loan->return_date ? $loan->return_date->format('d/m/Y') : '—' }}
                                    </td>
                                    
                                    <!-- Status -->
                                    <td class="py-3 px-4">
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

                <!-- Pagination -->
                <div class="mt-4 border-t border-slate-850/30 pt-3">
                    {{ $loans->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- Reviews and Ratings Section -->
    <div class="space-y-6">
        @if(Auth::user()?->isLibraryUser())
            <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 shadow-xl space-y-4">
                <h3 class="text-lg font-bold text-slate-100">{{ $userReview ? 'Editar Minha Avaliação' : 'Deixar uma Avaliação' }}</h3>
                <form action="{{ route('catalog.reviews.store', $item->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Sua Nota (Estrelas)</label>
                        <!-- Star Selection Input -->
                        <div class="flex items-center gap-2">
                            @for($i = 1; $i <= 5; $i++)
                                <label class="cursor-pointer">
                                    <input type="radio" name="rating" value="{{ $i }}" {{ ($userReview && $userReview->rating == $i) ? 'checked' : ($i == 5 ? 'checked' : '') }} class="sr-only peer">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-slate-700 hover:text-amber-400 peer-checked:text-amber-400 peer-checked:fill-amber-400 transition duration-150">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499c.15-.36.66-.36.81 0l1.83 4.245 4.56.368c.393.03.548.513.257.8l-3.393 3.125 1.01 4.5c.088.39-.31.68-.667.477L12 14.656l-3.924 2.15c-.356.202-.756-.087-.668-.477l1.01-4.5L5.025 8.78c-.29-.286-.135-.77.257-.8l4.56-.369 1.83-4.245z" />
                                    </svg>
                                </label>
                            @endfor
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="comment" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Seu Comentário (opcional, máximo 1000 caracteres)</label>
                        <textarea id="comment" name="comment" rows="3" maxlength="1000" placeholder="O que achou deste livro?" 
                            class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-550 focus:outline-none focus:border-blue-500 transition duration-150">{{ $userReview ? $userReview->comment : '' }}</textarea>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-primary hover:bg-primary-hover text-white font-medium px-5 py-2 rounded-xl text-sm transition duration-150 shadow-md shadow-emerald-950/20">
                            {{ $userReview ? 'Atualizar Avaliação' : 'Enviar Avaliação' }}
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Comments List -->
        <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 shadow-xl space-y-6">
            <h3 class="text-lg font-bold text-slate-100">Comentários e Críticas</h3>
            
            @if($reviews->isEmpty())
                <p class="text-slate-500 text-sm text-center py-6">Nenhuma crítica ou comentário para este livro ainda.</p>
            @else
                <div class="divide-y divide-slate-850/50 space-y-6">
                    @foreach($reviews as $review)
                        <div class="pt-6 first:pt-0 flex flex-col md:flex-row md:items-start justify-between gap-4">
                            <div class="space-y-2">
                                <div class="flex items-center gap-3">
                                    <span class="font-bold text-sm text-slate-200">{{ $review->user->name }}</span>
                                    <div class="flex text-amber-400">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5 {{ $i <= $review->rating ? 'text-amber-400' : 'text-slate-750' }}">
                                                <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd" />
                                            </svg>
                                        @endfor
                                    </div>
                                </div>
                                <span class="block text-[10px] text-slate-500">{{ $review->created_at->format('d/m/Y H:i') }}</span>
                                @if($review->comment)
                                    <p class="text-sm text-slate-400 font-light italic">"{{ $review->comment }}"</p>
                                @else
                                    <p class="text-xs text-slate-500 italic">Usuário apenas atribuiu nota, sem comentários.</p>
                                @endif
                            </div>
                            
                            @if(Auth::user()?->isCurator())
                                <div class="flex-shrink-0">
                                    <form action="{{ route('catalog.reviews.destroy', $review->id) }}" method="POST" onsubmit="return confirm('Deseja realmente apagar esta avaliação?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-500/10 text-red-400 border border-red-500/15 hover:bg-red-500/20 transition duration-150">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.34 9m-4.72 0-.34-9m9.03-5.42a48.908 48.908 0 0 0-3.92-.4c-3.18-.18-6.36-.18-9.54 0-1.353.11-2.42 1.22-2.42 2.56L4.5 9v11.5a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25V9a2.25 2.25 0 0 0-2.25-2.25m-1.405-1.405-1.497-1.497a2.25 2.25 0 0 0-3.18 0L9.435 4.5M21 12H3" />
                                            </svg>
                                            Apagar
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Pagination for Reviews -->
                <div class="mt-6 border-t border-slate-850/30 pt-4">
                    {{ $reviews->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- COinS metadata for Zotero -->
<span class="Z3988" title="ctx_ver=Z39.88-2004&amp;rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Abook&amp;rft.genre=book&amp;rft.btitle={{ urlencode($item->title) }}&amp;rft.author={{ urlencode($dublinCore['creator']) }}&amp;rft.pub={{ urlencode($dublinCore['publisher']) }}&amp;rft.date={{ urlencode($dublinCore['date']) }}&amp;rft.isbn={{ urlencode($item->isbn) }}"></span>

<!-- Zotero JSON-LD metadata -->
<script type="application/ld+json">
{
    "{!! '@' !!}context": "https://schema.org",
    "{!! '@' !!}type": "Book",
    "name": "{{ $item->title }}",
    "description": {!! json_encode($synopsis) !!},
    "author": {
        "{!! '@' !!}type": "Person",
        "name": "{{ $dublinCore['creator'] }}"
    },
    "publisher": {
        "{!! '@' !!}type": "Organization",
        "name": "{{ $dublinCore['publisher'] }}"
    },
    "datePublished": "{{ $dublinCore['date'] }}",
    "isbn": "{{ $item->isbn }}"
}
</script>

<!-- Share Modal Backdrop -->
<div id="share-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-sm hidden">
    <!-- Modal Panel -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 max-w-md w-full mx-4 shadow-2xl relative transform transition duration-300 scale-95 opacity-0" id="share-modal-panel">
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-850">
            <h3 class="text-sm uppercase font-bold text-slate-400 tracking-wider">Compartilhar Obra</h3>
            <button onclick="closeShareModal()" class="text-slate-500 hover:text-slate-350 transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Social Icons Row -->
        <div class="flex items-center justify-around gap-2 py-6 text-center">
            <!-- WhatsApp -->
            <a href="https://api.whatsapp.com/send?text={{ rawurlencode($item->title) }}%20-%20{{ urlencode($shortUrl) }}" target="_blank" class="flex flex-col items-center gap-1.5 group">
                <div class="w-12 h-12 rounded-full text-white flex items-center justify-center shadow-md hover:scale-105 active:scale-95 transition duration-150" style="background-color: #25D366;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M12.031 2a9.978 9.978 0 0 0-9.969 9.97c0 1.96.562 3.79 1.53 5.34l-1.02 3.72 3.82-1.002a9.92 9.92 0 0 0 4.64 1.162h.004a9.977 9.977 0 0 0 9.969-9.97A9.977 9.977 0 0 0 12.03 2m0 1.5a8.478 8.478 0 0 1 8.469 8.47 8.478 8.478 0 0 1-8.469 8.47 8.43 8.43 0 0 1-4.09-1.05l-.29-.17-2.31.6.61-2.22-.19-.3A8.44 8.44 0 0 1 3.53 11.97 8.478 8.478 0 0 1 12.03 3.5m-3.4 3.71c-.13 0-.32.05-.49.24-.17.18-.67.66-.67 1.6s.69 1.86.79 2c.09.13 1.35 2.06 3.28 2.89.46.2 1.02.32 1.37.37.5.07.96.05 1.32-.01.4-.06 1.23-.5 1.4-1 .17-.49.17-.91.12-1-.05-.08-.18-.13-.38-.23s-1.2-.59-1.39-.65-.32-.1-.46.1c-.14.2-.55.7-.68.85-.12.14-.25.16-.45.06-.2-.1-1.07-.4-2.04-1.26-.75-.67-1.25-1.5-1.4-1.74-.15-.24-.02-.37.1-.5.11-.11.25-.29.38-.44.13-.15.17-.25.26-.41.09-.17.04-.32-.02-.45s-.62-1.5-.85-2.04c-.23-.55-.49-.47-.67-.48Z"/>
                    </svg>
                </div>
                <span class="text-[10px] text-slate-500 font-bold">WhatsApp</span>
            </a>

            <!-- Twitter / X -->
            <a href="https://twitter.com/intent/tweet?url={{ urlencode($shortUrl) }}&text={{ urlencode($item->title) }}" target="_blank" class="flex flex-col items-center gap-1.5 group">
                <div class="w-12 h-12 rounded-full text-white flex items-center justify-center shadow-md hover:scale-105 active:scale-95 transition duration-150" style="background-color: #000000;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                    </svg>
                </div>
                <span class="text-[10px] text-slate-500 font-bold">Twitter</span>
            </a>

            <!-- Facebook -->
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shortUrl) }}" target="_blank" class="flex flex-col items-center gap-1.5 group">
                <div class="w-12 h-12 rounded-full text-white flex items-center justify-center shadow-md hover:scale-105 active:scale-95 transition duration-150" style="background-color: #1877F2;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.8c4.56-.93 8-4.96 8-9.8z"/>
                    </svg>
                </div>
                <span class="text-[10px] text-slate-500 font-bold">Facebook</span>
            </a>

            <!-- LinkedIn -->
            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($shortUrl) }}" target="_blank" class="flex flex-col items-center gap-1.5 group">
                <div class="w-12 h-12 rounded-full text-white flex items-center justify-center shadow-md hover:scale-105 active:scale-95 transition duration-150" style="background-color: #0077B5;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.32 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.79M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"/>
                    </svg>
                </div>
                <span class="text-[10px] text-slate-500 font-bold">LinkedIn</span>
            </a>

            <!-- Email -->
            <a href="mailto:?subject={{ rawurlencode('Confira esta obra no Biblió') }}&body={{ rawurlencode('Veja esta incrível obra catalogada: ' . $item->title . "\n\n" . $shortUrl) }}" class="flex flex-col items-center gap-1.5 group">
                <div class="w-12 h-12 rounded-full text-white flex items-center justify-center shadow-md hover:scale-105 active:scale-95 transition duration-150" style="background-color: #64748B;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m3 7l9 6l9-6M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z"/>
                    </svg>
                </div>
                <span class="text-[10px] text-slate-500 font-bold">E-mail</span>
            </a>
        </div>

        <!-- Copy Link Field -->
        <div class="space-y-1.5">
            <label class="block text-xs uppercase font-bold text-slate-500 tracking-wider">Link Curto</label>
            <div class="flex gap-2">
                <input type="text" id="share-link-input" readonly value="{{ $shortUrl }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-300 focus:outline-none">
                <button onclick="copyShareLink()" id="copy-btn" class="bg-primary hover:bg-primary-hover text-white font-semibold px-4 py-2.5 rounded-xl text-xs transition duration-150 flex-shrink-0">
                    Copiar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openShareModal() {
        const modal = document.getElementById('share-modal');
        const panel = document.getElementById('share-modal-panel');
        modal.classList.remove('hidden');
        setTimeout(() => {
            panel.classList.remove('scale-95', 'opacity-0');
            panel.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    // Close share modal
    function closeShareModal() {
        const modal = document.getElementById('share-modal');
        const panel = document.getElementById('share-modal-panel');
        panel.classList.remove('scale-100', 'opacity-100');
        panel.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    // Copy direct short link
    function copyShareLink() {
        const input = document.getElementById('share-link-input');
        const btn = document.getElementById('copy-btn');
        input.select();
        navigator.clipboard.writeText(input.value).then(() => {
            btn.innerText = 'Copiado!';
            btn.classList.remove('bg-primary', 'hover:bg-primary-hover');
            btn.classList.add('bg-emerald-600', 'hover:bg-emerald-500');
            setTimeout(() => {
                btn.innerText = 'Copiar';
                btn.classList.remove('bg-emerald-600', 'hover:bg-emerald-500');
                btn.classList.add('bg-primary', 'hover:bg-primary-hover');
            }, 2000);
        });
    }

    // Modal click outside to close
    document.getElementById('share-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeShareModal();
        }
    });

    // Close modal on Escape key press
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeShareModal();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Synopsis toggle
        const btnToggleSynopsis = document.getElementById('btn-toggle-synopsis');
        const synopsisTruncated = document.getElementById('synopsis-truncated');
        const synopsisFull = document.getElementById('synopsis-full');

        if (btnToggleSynopsis && synopsisTruncated && synopsisFull) {
            btnToggleSynopsis.addEventListener('click', function() {
                const isHidden = synopsisFull.classList.contains('hidden');
                if (isHidden) {
                    synopsisFull.classList.remove('hidden');
                    synopsisTruncated.classList.add('hidden');
                    btnToggleSynopsis.textContent = 'Mostrar menos';
                } else {
                    synopsisFull.classList.add('hidden');
                    synopsisTruncated.classList.remove('hidden');
                    btnToggleSynopsis.textContent = 'Mostrar mais';
                }
            });
        }

        // Tags toggle
        const btnToggleTags = document.getElementById('btn-toggle-tags');
        const extraTags = document.querySelectorAll('.extra-tag');

        if (btnToggleTags && extraTags.length > 0) {
            btnToggleTags.addEventListener('click', function() {
                const isHidden = extraTags[0].classList.contains('hidden');
                extraTags.forEach(tag => {
                    if (isHidden) {
                        tag.classList.remove('hidden');
                    } else {
                        tag.classList.add('hidden');
                    }
                });
                if (isHidden) {
                    btnToggleTags.textContent = 'Ver menos';
                    btnToggleTags.classList.add('bg-blue-600/10');
                } else {
                    btnToggleTags.textContent = 'Ver mais (+' + extraTags.length + ')';
                    btnToggleTags.classList.remove('bg-blue-600/10');
                }
            });
        }
    });
</script>
@endsection
