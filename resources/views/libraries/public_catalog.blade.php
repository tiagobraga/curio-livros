@php
    $colorTheme = $library->getColorTheme();
    $customLogo = $library->custom_logo_url ?: '/images/bibliologo.jpg';
    $customSubtitle = $library->subtitle ?: __('Biblioteca Inteligente');
    $currentLocale = App::getLocale();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $currentLocale) }}" class="h-full bg-brand-creme text-brand-text">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Catálogo Público') }} — {{ $library->name }}</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind and Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
        :root {
            --color-primary: {{ $colorTheme['primary'] }};
            --color-primary-hover: {{ $colorTheme['primary_hover'] }};
        }
        .bg-primary { background-color: {{ $colorTheme['primary'] }} !important; }
        .hover\:bg-primary-hover:hover { background-color: {{ $colorTheme['primary_hover'] }} !important; }
        .text-primary { color: {{ $colorTheme['primary'] }} !important; }
        .border-primary { border-color: {{ $colorTheme['primary'] }} !important; }
    </style>
</head>
<body class="min-h-full flex flex-col justify-between bg-brand-creme relative overflow-x-hidden pt-1.5 px-6 pb-6">

    <!-- Top Accent Line -->
    <div class="h-1.5 bg-primary w-full absolute top-0 left-0 z-50"></div>

    <!-- Main Container -->
    <div class="w-full max-w-5xl mx-auto flex-1 py-10 z-10 space-y-8">
        
        <!-- Header / Library Details Card -->
        <div class="bg-white border border-brand-border rounded-3xl p-6 md:p-8 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5 max-w-2xl">
                <img src="{{ $customLogo }}" alt="Logo {{ $library->name }}" class="w-24 h-auto max-h-24 object-contain rounded-2xl bg-white border border-brand-border p-1 shadow-sm flex-shrink-0">
                <div class="space-y-2">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-[10px] bg-primary/10 text-primary border border-primary/20 px-2.5 py-1 rounded-full font-bold uppercase tracking-wider">
                            {{ $library->library_type ?: 'Pública' }}
                        </span>
                        <span class="text-[10px] bg-emerald-500/10 text-emerald-700 border border-emerald-500/20 px-2.5 py-1 rounded-full font-bold uppercase tracking-wider">
                            {{ __('Catálogo Público') }}
                        </span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900">{{ $library->name }}</h1>
                    <p class="text-xs font-semibold text-primary/80">{{ $customSubtitle }}</p>
                    @if($library->description)
                        <p class="text-xs text-slate-500 leading-relaxed">{{ $library->description }}</p>
                    @endif
                    @if($library->address)
                        <div class="flex items-center gap-2 text-xs text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-slate-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25s-7.5-4.108-7.5-11.25a7.5 7.5 0 1 1 15 0Z" />
                            </svg>
                            <span>{{ $library->address }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Share URL Clipboard Widget -->
            <div class="bg-brand-creme/50 border border-brand-border/60 rounded-2xl p-4 flex flex-col justify-center min-w-[240px]">
                <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider mb-2 block">{{ __('Link de Compartilhamento') }}</span>
                <div class="flex gap-2">
                    <input type="text" id="share-link-input" readonly value="{{ $shortUrl }}"
                        class="bg-white border border-brand-border/80 rounded-xl px-3 py-1.5 text-xs text-slate-600 focus:outline-none flex-1 truncate font-mono">
                    <button type="button" onclick="copyShareLink()" id="btn-copy"
                        class="bg-primary hover:bg-primary-hover text-white rounded-xl px-3.5 py-1.5 text-xs font-bold transition flex items-center gap-1 active:scale-95 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5" id="copy-icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H5.25m11.9-3.664A2.251 2.251 0 0 0 15 2.25h-1.5a2.251 2.251 0 0 0-2.15 1.586m5.8 0c.065.21.1.433.1.664v.75h-6V4.5c0-.231.035-.454.1-.664M6.75 7.5h.008v.008H6.75V7.5Zm0 3h.008v.008H6.75v-.008Zm0 3h.008v.008H6.75v-.008Z" />
                        </svg>
                        <span id="copy-text">{{ __('Copiar Link') }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Search Bar & Statistics -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="relative w-full sm:max-w-md">
                <input type="text" id="catalog-search" oninput="filterBooks()" placeholder="{{ __('Buscar') }}..."
                    class="w-full bg-white border border-brand-border rounded-2xl pl-10 pr-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:border-primary transition duration-150 shadow-sm" />
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
                </svg>
            </div>
            <div class="text-xs text-slate-500 font-medium">
                {{ __('Catálogo') }}: <strong class="text-primary text-sm font-bold" id="total-copies-count">{{ $copies->count() }}</strong> exemplares
            </div>
        </div>

        <!-- Books Grid -->
        <div id="books-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($copies as $copy)
                @php
                    $item = $copy->centralItem;
                    $meta = $item->metadata;
                    $author = $meta['author'] ?? 'Autor desconhecido';
                    $publisher = $meta['publisher'] ?? 'N/A';
                    $year = $meta['publication_year'] ?? 'N/A';
                    $cover = $meta['cover_url'] ?? null;
                    $subjects = $meta['subjects'] ?? [];
                @endphp
                <div class="book-card bg-white border border-brand-border rounded-3xl overflow-hidden shadow-sm hover:shadow-md transition duration-300 flex flex-col justify-between"
                     data-title="{{ strtolower($item->title) }}" data-author="{{ strtolower($author) }}" data-subjects="{{ strtolower(implode(' ', $subjects)) }}">
                    
                    <div class="p-5 space-y-4">
                        <!-- Cover & Details -->
                        <div class="flex gap-4">
                            <!-- Cover Book -->
                            <div class="w-20 h-28 bg-slate-100 rounded-xl overflow-hidden border border-brand-border/60 flex-shrink-0 flex items-center justify-center p-1">
                                @if($cover)
                                    <img src="{{ $cover }}" alt="Capa" class="w-full h-full object-cover rounded-lg">
                                @else
                                    <div class="flex flex-col items-center justify-center h-full text-center p-2 text-slate-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 mb-1">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                        </svg>
                                        <span class="text-[8px] font-bold uppercase tracking-wider">Sem capa</span>
                                    </div>
                                @endif
                            </div>

                            <div class="space-y-1 truncate-3-lines">
                                <h3 class="font-bold text-slate-800 text-sm line-clamp-2" title="{{ $item->title }}">{{ $item->title }}</h3>
                                <p class="text-xs text-slate-500 font-semibold">{{ $author }}</p>
                                <p class="text-[10px] text-slate-400">{{ $publisher }} • {{ $year }}</p>
                                
                                @if($copy->call_number)
                                    <div class="pt-2">
                                        <span class="text-[9px] font-mono bg-slate-100 text-slate-600 px-2 py-0.5 rounded-lg border border-slate-200">
                                            Call: {{ $copy->call_number }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Subjects / Tags -->
                        @if(!empty($subjects))
                            <div class="flex flex-wrap gap-1">
                                @foreach(array_slice($subjects, 0, 3) as $subject)
                                    <span class="text-[9px] bg-slate-150 text-slate-600 px-2 py-0.5 rounded-md font-medium">
                                        {{ $subject }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Card Footer Detail Link -->
                    <div class="px-5 py-3 border-t border-brand-border/60 bg-slate-50/50 flex justify-between items-center">
                        <span class="text-xs font-semibold text-emerald-700 bg-emerald-500/10 px-2.5 py-1 rounded-full border border-emerald-500/20">
                            Disponível ({{ $copy->quantity }})
                        </span>
                        <a href="{{ route('catalog.shared', ['code' => $item->share_code ?: $item->id, 'lib' => $library->share_code]) }}" target="_blank"
                            class="text-xs text-primary font-bold hover:underline inline-flex items-center gap-1">
                            Ver livro
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5">
                                <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center text-slate-400 bg-white border border-brand-border rounded-3xl">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-12 h-12 mx-auto mb-3 text-slate-300">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                    </svg>
                    <p class="text-sm font-semibold">{{ __('Nenhum livro encontrado') }}</p>
                </div>
            @endforelse
        </div>

        <div id="no-results" class="hidden py-12 text-center text-slate-400 bg-white border border-brand-border rounded-3xl">
            <p class="text-sm font-semibold">{{ __('Nenhum livro encontrado') }}</p>
        </div>

    </div>

    <!-- Footer -->
    <footer class="w-full max-w-5xl mx-auto border-t border-brand-border/60 pt-6 mt-10 text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <span class="font-bold bg-slate-200/80 px-2 py-0.5 rounded mr-1">CC-BY</span>
            <a href="https://curio.ia.br" target="_blank" rel="noopener noreferrer" class="hover:text-primary transition underline underline-offset-2">
                {{ __('Uma iniciativa open source do grupo de pesquisa Curió') }}
            </a>
        </div>
        <div>
            Catálogo gerado via <a href="https://biblioteca.curio.ia.br" target="_blank" class="font-semibold text-primary hover:underline">Biblió</a>.
        </div>
    </footer>

    <script>
        function copyShareLink() {
            const input = document.getElementById('share-link-input');
            input.select();
            input.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(input.value).then(() => {
                const copyText = document.getElementById('copy-text');
                const btn = document.getElementById('btn-copy');
                
                copyText.innerText = 'Copiado!';
                btn.classList.remove('bg-primary', 'hover:bg-primary-hover');
                btn.classList.add('bg-emerald-600');
                
                setTimeout(() => {
                    copyText.innerText = 'Copiar';
                    btn.classList.remove('bg-emerald-600');
                    btn.classList.add('bg-primary', 'hover:bg-primary-hover');
                }, 2000);
            });
        }

        function filterBooks() {
            const query = document.getElementById('catalog-search').value.toLowerCase().trim();
            const cards = document.querySelectorAll('.book-card');
            let visibleCount = 0;

            cards.forEach(card => {
                const title = card.getAttribute('data-title');
                const author = card.getAttribute('data-author');
                const subjects = card.getAttribute('data-subjects');

                if (title.includes(query) || author.includes(query) || subjects.includes(query)) {
                    card.classList.remove('hidden');
                    visibleCount++;
                } else {
                    card.classList.add('hidden');
                }
            });

            document.getElementById('total-copies-count').innerText = visibleCount;

            const noResults = document.getElementById('no-results');
            if (visibleCount === 0 && cards.length > 0) {
                noResults.classList.remove('hidden');
            } else {
                noResults.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
