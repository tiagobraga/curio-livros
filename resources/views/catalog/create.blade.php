@extends('layouts.app')

@section('title', __('Catalogar Livro'))

@section('content')
<div class="space-y-8">
    <!-- Back Link & Header -->
    <div>
        <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-slate-200 transition duration-150">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            {{ __('Voltar ao catálogo') }}
        </a>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-100">{{ __('Catalogar Novo Livro') }}</h1>
            <p class="text-sm text-slate-400 mt-1">{{ __('Insira o ISBN para buscar automaticamente os metadados bibliográficos ou preencha manualmente.') }}</p>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column: Search & Camera / Preview -->
        <div class="space-y-6">
            <!-- ISBN Search Box -->
            <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 shadow-xl space-y-4">
                <h2 class="text-lg font-bold text-slate-200">{{ __('Busca Rápida por Código') }}</h2>
                
                <div class="space-y-3">
                    <div class="relative">
                        <input type="text" id="isbn_search" placeholder="{{ __('Digite o ISBN (ex: 9788535902778)...') }}" 
                            class="w-full bg-slate-900/50 border border-slate-800 rounded-xl pl-4 pr-12 py-3 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-200">
                        <button type="button" onclick="performLookup()" class="absolute right-2 top-2 p-1.5 bg-primary hover:bg-primary-hover text-white rounded-lg text-xs font-semibold transition duration-150">
                            {{ __('Buscar') }}
                        </button>
                    </div>

                    <div class="flex gap-2">
                        <button type="button" id="start-btn" onclick="startScanner()" class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700/80 font-medium py-2.5 rounded-xl text-xs transition duration-150 flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-blue-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 47.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                            </svg>
                            {{ __('Usar Câmera (Scanner)') }}
                        </button>
                        <button type="button" id="stop-btn" onclick="stopScanner()" style="display: none;" class="flex-1 bg-red-950/40 text-red-400 border border-red-900/50 hover:bg-red-900/20 font-medium py-2.5 rounded-xl text-xs transition duration-150 flex items-center justify-center gap-2">
                            {{ __('Parar Câmera') }}
                        </button>
                    </div>
                </div>

                <!-- Camera Scanner Box -->
                <div id="reader-container" class="hidden relative mt-4 overflow-hidden rounded-2xl border border-slate-800 bg-slate-950 flex flex-col items-center">
                    <div id="reader" class="w-full aspect-square"></div>
                    <div class="p-3 bg-slate-900 w-full text-center text-[10px] text-slate-500 border-t border-slate-800">
                        {{ __('Aponte a câmera traseira do celular para o código de barras (EAN-13/ISBN).') }}
                    </div>
                </div>

                <!-- Loading Spinner Box -->
                <div id="loading-box" style="display: none;" class="py-12 flex flex-col items-center justify-center bg-slate-900/20 rounded-2xl border border-slate-850">
                    <div class="w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                    <span class="text-xs text-slate-400 mt-3 font-medium">{{ __('Buscando metadados...') }}</span>
                </div>
            </div>

            <!-- Cover Preview Box -->
            <div id="cover-preview-box" class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 shadow-xl flex flex-col items-center justify-center text-center space-y-4">
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500 w-full text-left">{{ __('Pré-visualização da Capa') }}</span>
                
                <div class="w-48 aspect-[3/4] bg-slate-950 rounded-2xl border border-slate-800/80 overflow-hidden flex items-center justify-center relative">
                    <img id="cover_preview" src="" alt="{{ __('Capa') }}" class="w-full h-full object-cover hidden">
                    <div id="cover_placeholder" class="text-slate-700 flex flex-col items-center justify-center p-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-16 h-16 mb-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                        </svg>
                        <span class="text-[10px] uppercase tracking-wide">{{ __('Sem imagem da capa') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Registration Form -->
        <div class="lg:col-span-2">
            <form action="{{ route('catalog.store') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="item_type" value="book">
                <input type="hidden" id="cover_url" name="cover_url">

                <!-- Section 1: BIBFRAME Bibliographic Metadata (Work/Instance) -->
                <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 md:p-8 shadow-xl space-y-6">
                    <h2 class="text-xl font-bold text-slate-100 flex items-center gap-2 border-b border-slate-850 pb-4">
                        <span class="p-1.5 bg-blue-500/10 text-blue-400 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                            </svg>
                        </span>
                        {{ __('Metadados Bibliográficos (Central)') }}
                    </h2>

                    <!-- Notification Badge for Lookup Source (Internal DB vs External APIs) -->
                    <div id="source-badge" class="hidden rounded-2xl p-4 text-xs font-medium border flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0 text-emerald-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                        <span id="source-text"></span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Title -->
                        <div class="md:col-span-2 space-y-1.5">
                            <label for="title" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Título') }} <span class="text-red-500">*</span></label>
                            <input type="text" id="title" name="title" required value="{{ old('title') }}"
                                class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                            @error('title') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Author -->
                        <div class="space-y-1.5">
                            <label for="author" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Autor / Criador') }}</label>
                            <input type="text" id="author" name="author" value="{{ old('author') }}"
                                class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                        </div>

                        <!-- ISBN -->
                        <div class="space-y-1.5">
                            <label for="isbn" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('ISBN') }}</label>
                            <input type="text" id="isbn" name="isbn" value="{{ old('isbn') }}"
                                class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                        </div>

                        <!-- Publisher -->
                        <div class="space-y-1.5">
                            <label for="publisher" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Editora') }}</label>
                            <input type="text" id="publisher" name="publisher" value="{{ old('publisher') }}"
                                class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                        </div>

                        <!-- Publication Year & Pages -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label for="publication_year" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Ano') }}</label>
                                <input type="text" id="publication_year" name="publication_year" maxlength="4" value="{{ old('publication_year') }}"
                                    class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                            </div>
                            <div class="space-y-1.5">
                                <label for="pages" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Páginas') }}</label>
                                <input type="number" id="pages" name="pages" value="{{ old('pages') }}"
                                    class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                            </div>
                        </div>

                        <!-- Subjects / Tags -->
                        <div class="md:col-span-2 space-y-1.5">
                            <label for="subjects" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Assuntos / Tags') }} <span class="text-[10px] text-slate-500 lowercase normal-case">({{ __('separados por vírgula') }})</span></label>
                            <input type="text" id="subjects" name="subjects" value="{{ old('subjects') }}"
                                placeholder="{{ __('Literatura Brasileira, Romance, História, etc.') }}"
                                class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2 space-y-1.5">
                            <label for="description" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Sinopse / Descrição') }}</label>
                            <textarea id="description" name="description" rows="4"
                                class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 2: BIBFRAME Item / Local physical copy metadata -->
                <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 md:p-8 shadow-xl space-y-6">
                    <h2 class="text-xl font-bold text-slate-100 flex items-center gap-2 border-b border-slate-850 pb-4">
                        <span class="p-1.5 bg-indigo-500/10 text-indigo-400 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25s-7.5-4.108-7.5-11.25a7.5 7.5 0 1 1 15 0Z" />
                            </svg>
                        </span>
                        {{ __('Dados do Exemplar Físico (Local)') }}
                    </h2>

                    <div class="space-y-5">
                        <!-- Library Selector -->
                        <div class="space-y-1.5">
                            <label for="library_id" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Biblioteca / Unidade') }} <span class="text-red-500">*</span></label>
                            <select id="library_id" name="library_id" required
                                class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                                @foreach($libraries as $library)
                                    <option value="{{ $library->id }}" {{ old('library_id') == $library->id ? 'selected' : '' }}>
                                        {{ $library->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('library_id') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <!-- Call Number -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label for="call_number" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Nº de Chamada') }}</label>
                                <span id="auto-call-number-badge" class="hidden text-[10px] text-emerald-400 font-medium bg-emerald-500/10 border border-emerald-500/20 px-1.5 py-0.5 rounded-md">
                                    ✨ {{ __('Automático') }}
                                </span>
                            </div>
                            <input type="text" id="call_number" name="call_number" placeholder="{{ __('ex: 869 M149d') }}" value="{{ old('call_number') }}"
                                class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                        </div>

                        <!-- Shelf Location -->
                        <div class="space-y-1.5">
                            <label for="shelf_location" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Estante / Prateleira') }}</label>
                            <input type="text" id="shelf_location" name="shelf_location" placeholder="{{ __('ex: Corredor A, Estante 3') }}" value="{{ old('shelf_location') }}"
                                class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                        </div>

                        <!-- Quantity -->
                        <div class="space-y-1.5">
                            <label for="quantity" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Quantidade') }} <span class="text-red-500">*</span></label>
                            <input type="number" id="quantity" name="quantity" required min="1" value="{{ old('quantity', 1) }}"
                                class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                            @error('quantity') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Hidden input for custom field values -->
                    <input type="hidden" name="custom_fields_values" id="custom_fields_values_json" value="">

                    <!-- Dynamic Library Custom Fields Container -->
                    <div id="library-custom-fields-wrapper" class="hidden pt-4 border-t border-slate-800 space-y-3">
                        <span class="block text-xs uppercase font-bold text-slate-300 tracking-wider flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            {{ __('Campos Personalizados Desta Unidade') }}
                        </span>
                        <div id="library-custom-fields-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            <!-- Preenchido dinamicamente via JS -->
                        </div>
                    </div>
                </div>
            </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('catalog.index') }}" class="bg-slate-900 hover:bg-slate-800 text-slate-400 border border-slate-800 font-medium px-6 py-3 rounded-xl text-sm transition duration-150">
                        {{ __('Cancelar') }}
                    </a>
                    <button type="submit" class="bg-primary hover:bg-primary-hover text-white font-medium px-8 py-3 rounded-xl text-sm transition duration-150 shadow-lg shadow-emerald-950/15">
                        {{ __('Confirmar e Salvar no Acervo') }}
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    let html5QrCode = null;

    function startScanner() {
        const container = document.getElementById('reader-container');
        const startBtn = document.getElementById('start-btn');
        const stopBtn = document.getElementById('stop-btn');

        container.classList.remove('hidden');
        startBtn.style.display = 'none';
        stopBtn.style.display = 'inline-flex';

        html5QrCode = new Html5Qrcode("reader", {
            formatsToSupport: [
                Html5QrcodeSupportedFormats.EAN_13,
                Html5QrcodeSupportedFormats.EAN_8,
                Html5QrcodeSupportedFormats.UPC_A,
                Html5QrcodeSupportedFormats.UPC_E,
                Html5QrcodeSupportedFormats.CODE_128
            ]
        });
        
        html5QrCode.start(
            { facingMode: "environment" },
            {
                fps: 15,
                qrbox: function(width, height) {
                    // Retângulo perfeito para código de barras EAN-13
                    const boxWidth = Math.min(width * 0.85, 320);
                    const boxHeight = Math.min(height * 0.4, 160);
                    return { width: boxWidth, height: boxHeight };
                }
            },
            (decodedText, decodedResult) => {
                // Ao ler com sucesso o código
                document.getElementById('isbn_search').value = decodedText;
                document.getElementById('isbn').value = decodedText;
                stopScanner();
                lookupIsbn(decodedText);
            },
            (errorMessage) => {
                // Ignora erros verbosos de leitura frame a frame
            }
        ).catch(err => {
            console.error("Erro ao acessar a câmera: ", err);
            alert("{{ __('Não foi possível acessar a câmera. Verifique se deu permissão e se está em uma página HTTPS.') }}");
            stopScanner();
        });
    }

    function stopScanner() {
        const container = document.getElementById('reader-container');
        const startBtn = document.getElementById('start-btn');
        const stopBtn = document.getElementById('stop-btn');

        startBtn.style.display = 'inline-flex';
        stopBtn.style.display = 'none';
        container.classList.add('hidden');

        if (html5QrCode) {
            html5QrCode.stop().then(() => {
                html5QrCode = null;
            }).catch(err => {
                console.error("Erro ao parar câmera: ", err);
            });
        }
    }

    function performLookup() {
        const val = document.getElementById('isbn_search').value.trim();
        if (!val) {
            alert('{{ __("Por favor, digite um ISBN válido.") }}');
            return;
        }
        lookupIsbn(val);
    }

    let lastLookedUpIsbn = '';

    function lookupIsbn(isbn) {
        const cleanIsbn = isbn.replace(/[^0-9]/g, '');
        if (!cleanIsbn) return;
        lastLookedUpIsbn = cleanIsbn;

        // Exibe o loading e limpa preview da capa
        const loadingBox = document.getElementById('loading-box');
        loadingBox.style.display = 'flex';

        fetch(`/catalog/lookup?isbn=${encodeURIComponent(cleanIsbn)}`)
            .then(res => {
                if (!res.ok) {
                    throw new Error('{{ __("Livro não localizado nas bases de dados") }}');
                }
                return res.json();
            })
            .then(data => {
                loadingBox.style.display = 'none';
                
                // Preenche campos
                if (data.title) document.getElementById('title').value = data.title;
                if (data.author) document.getElementById('author').value = data.author;
                if (data.isbn) document.getElementById('isbn').value = data.isbn;
                if (data.publisher) document.getElementById('publisher').value = data.publisher;
                if (data.publication_year) document.getElementById('publication_year').value = data.publication_year;
                if (data.pages) document.getElementById('pages').value = data.pages;
                if (data.description) document.getElementById('description').value = data.description;
                
                if (data.subjects && Array.isArray(data.subjects)) {
                    document.getElementById('subjects').value = data.subjects.join(', ');
                }

                // Trata o badge informativo de origem dos dados (Base Interna vs APIs Externas)
                const sourceBadge = document.getElementById('source-badge');
                const sourceText = document.getElementById('source-text');
                if (sourceBadge && sourceText) {
                    sourceBadge.classList.remove('hidden', 'bg-emerald-500/10', 'border-emerald-500/20', 'text-emerald-300', 'bg-blue-500/10', 'border-blue-500/20', 'text-blue-300');
                    if (data.source === 'internal') {
                        sourceBadge.classList.add('bg-emerald-500/10', 'border-emerald-500/20', 'text-emerald-300');
                        sourceText.innerHTML = '<strong>' + @json(__('Obra localizada no catálogo interno!')) + '</strong> ' + @json(__('Os dados cadastrados na base foram autopreenchidos. Se você fizer alguma alteração nos metadados, ela será enviada como sugestão de edição para a curadoria.'));
                    } else {
                        sourceBadge.classList.add('bg-blue-500/10', 'border-blue-500/20', 'text-blue-300');
                        sourceText.innerHTML = '<strong>' + @json(__('Obra resgatada das bases externas na internet.')) + '</strong> ' + @json(__('Confira os dados preenchidos e confirme o cadastro.'));
                    }
                }

                // Trata capa
                const coverUrl = data.cover_url;
                const coverPreview = document.getElementById('cover_preview');
                const coverPlaceholder = document.getElementById('cover_placeholder');
                const coverInput = document.getElementById('cover_url');

                if (coverUrl) {
                    coverPreview.src = coverUrl;
                    coverPreview.classList.remove('hidden');
                    coverPlaceholder.classList.add('hidden');
                    coverInput.value = coverUrl;
                } else {
                    coverPreview.src = '';
                    coverPreview.classList.add('hidden');
                    coverPlaceholder.classList.remove('hidden');
                    coverInput.value = '';
                }
            })
            .catch(err => {
                loadingBox.style.display = 'none';
                console.error(err);
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const isbnInput = document.getElementById('isbn');
        const isbnSearchInput = document.getElementById('isbn_search');

        if (isbnInput) {
            isbnInput.addEventListener('blur', function() {
                const val = this.value.replace(/[^0-9]/g, '');
                if (val.length >= 10 && val !== lastLookedUpIsbn) {
                    if (isbnSearchInput) isbnSearchInput.value = this.value;
                    lookupIsbn(this.value);
                }
            });
        }

        if (isbnSearchInput) {
            isbnSearchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    performLookup();
                }
            });
        }

        const librarySelect = document.getElementById('library_id');
        if (librarySelect) {
            fetchLibraryCustomFields(librarySelect.value);
            librarySelect.addEventListener('change', function() {
                isCallNumberManuallyEdited = false;
                fetchLibraryCustomFields(this.value);
            });
        }

        const callNumberInput = document.getElementById('call_number');
        if (callNumberInput) {
            callNumberInput.addEventListener('input', function() {
                isCallNumberManuallyEdited = true;
                if (!this.value) {
                    isCallNumberManuallyEdited = false;
                    calculateCallNumber();
                }
            });
        }

        // Escuta alterações nos campos bibliográficos para recalcular o número de chamada em tempo real
        ['title', 'author', 'publisher', 'isbn', 'publication_year', 'pages', 'subjects', 'description'].forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener('input', calculateCallNumber);
            }
        });
    });

    // --- CAMPOS PERSONALIZADOS E GERAÇÃO AUTOMÁTICA DO NÚMERO DE CHAMADA ---
    let currentLibraryConfig = null;
    let customFieldValues = {};
    let isCallNumberManuallyEdited = false;

    function fetchLibraryCustomFields(libraryId) {
        if (!libraryId) {
            document.getElementById('library-custom-fields-wrapper').classList.add('hidden');
            return;
        }

        fetch(`/libraries/${libraryId}/custom-fields`)
            .then(res => res.json())
            .then(data => {
                currentLibraryConfig = data;
                renderLibraryCustomFields(data.custom_fields || []);
                calculateCallNumber();
            })
            .catch(err => console.error('Erro ao buscar campos da biblioteca:', err));
    }

    function renderLibraryCustomFields(fields) {
        const wrapper = document.getElementById('library-custom-fields-wrapper');
        const grid = document.getElementById('library-custom-fields-grid');
        grid.innerHTML = '';

        if (!fields || fields.length === 0) {
            wrapper.classList.add('hidden');
            return;
        }

        wrapper.classList.remove('hidden');
        fields.forEach(f => {
            const col = document.createElement('div');
            col.className = 'space-y-1';

            const labelEl = document.createElement('label');
            labelEl.className = 'block text-[11px] uppercase font-bold text-slate-400 tracking-wider';
            labelEl.textContent = f.label;
            col.appendChild(labelEl);

            if (f.type === 'select') {
                const select = document.createElement('select');
                select.className = 'w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-100 focus:outline-none focus:border-blue-500';
                select.dataset.customKey = f.key;
                
                const defaultOpt = document.createElement('option');
                defaultOpt.value = '';
                defaultOpt.textContent = '-- Selecione --';
                select.appendChild(defaultOpt);

                (f.options || []).forEach(opt => {
                    const option = document.createElement('option');
                    option.value = opt;
                    option.textContent = opt;
                    select.appendChild(option);
                });

                select.addEventListener('change', () => {
                    customFieldValues[f.key] = select.value;
                    updateCustomFieldValuesJson();
                    calculateCallNumber();
                });
                col.appendChild(select);
            } else if (f.type === 'boolean') {
                const checkLabel = document.createElement('label');
                checkLabel.className = 'inline-flex items-center gap-2 mt-1 cursor-pointer';
                const cb = document.createElement('input');
                cb.type = 'checkbox';
                cb.value = '1';
                cb.className = 'w-4 h-4 rounded border-slate-800 bg-slate-950 text-blue-600 focus:ring-blue-500';
                cb.dataset.customKey = f.key;
                cb.addEventListener('change', () => {
                    customFieldValues[f.key] = cb.checked ? '1' : '0';
                    updateCustomFieldValuesJson();
                    calculateCallNumber();
                });
                const span = document.createElement('span');
                span.className = 'text-xs text-slate-300';
                span.textContent = 'Sim';
                checkLabel.appendChild(cb);
                checkLabel.appendChild(span);
                col.appendChild(checkLabel);
            } else if (f.type === 'integer') {
                const input = document.createElement('input');
                input.type = 'number';
                input.className = 'w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-100 focus:outline-none focus:border-blue-500';
                input.dataset.customKey = f.key;
                if (f.auto_increment && f.suggested_value) {
                    input.value = f.suggested_value;
                    customFieldValues[f.key] = f.suggested_value;
                    input.placeholder = `Sugerido: ${f.suggested_value}`;
                } else if (f.auto_increment) {
                    input.placeholder = 'Automático (sequencial)';
                }
                input.addEventListener('input', () => {
                    customFieldValues[f.key] = input.value;
                    updateCustomFieldValuesJson();
                    calculateCallNumber();
                });
                col.appendChild(input);
                if (f.auto_increment && f.suggested_value) {
                    const hint = document.createElement('div');
                    hint.className = 'text-[9px] text-slate-400 mt-0.5';
                    hint.innerHTML = `Menor disponível: <strong class="text-emerald-400 font-mono">${f.suggested_value}</strong>`;
                    col.appendChild(hint);
                }
            } else {
                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-100 focus:outline-none focus:border-blue-500';
                input.dataset.customKey = f.key;
                input.addEventListener('input', () => {
                    customFieldValues[f.key] = input.value;
                    updateCustomFieldValuesJson();
                    calculateCallNumber();
                });
                col.appendChild(input);
            }

            grid.appendChild(col);
        });
        updateCustomFieldValuesJson();
    }

    function updateCustomFieldValuesJson() {
        document.getElementById('custom_fields_values_json').value = JSON.stringify(customFieldValues);
    }

    function calculateCallNumber() {
        if (!currentLibraryConfig || !currentLibraryConfig.has_call_number_format) {
            document.getElementById('auto-call-number-badge').classList.add('hidden');
            return;
        }

        const format = currentLibraryConfig.call_number_format;
        if (!format || format.length === 0) {
            document.getElementById('auto-call-number-badge').classList.add('hidden');
            return;
        }

        const callNumberInput = document.getElementById('call_number');
        if (isCallNumberManuallyEdited && callNumberInput.value.trim() !== '') {
            return;
        }

        const bookData = {
            title: document.getElementById('title')?.value || '',
            author: document.getElementById('author')?.value || '',
            publisher: document.getElementById('publisher')?.value || '',
            isbn: document.getElementById('isbn')?.value || '',
            publication_year: document.getElementById('publication_year')?.value || '',
            pages: document.getElementById('pages')?.value || '',
            subjects: document.getElementById('subjects')?.value || '',
            description: document.getElementById('description')?.value || ''
        };

        let parts = [];
        format.forEach(el => {
            if (el.type === 'static') {
                if (el.value) parts.push({ is_static: true, val: el.value });
            } else if (el.type === 'field') {
                let raw = '';
                if (el.source === 'standard') {
                    raw = bookData[el.key] || '';
                } else if (el.source === 'custom') {
                    raw = customFieldValues[el.key] || '';
                }

                let clean = raw.normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
                if (el.length && parseInt(el.length) > 0) {
                    clean = clean.substring(0, parseInt(el.length));
                }

                if (el.casing === 'upper') {
                    clean = clean.toUpperCase();
                } else if (el.casing === 'original') {
                    // original
                } else {
                    clean = clean.toLowerCase();
                }

                clean = clean.trim();
                if (clean !== '') {
                    parts.push({ is_static: false, val: clean });
                }
            }
        });

        let result = '';
        let lastWasStatic = false;
        parts.forEach(p => {
            if (p.is_static) {
                if (result !== '' && !lastWasStatic) {
                    result += p.val;
                    lastWasStatic = true;
                }
            } else {
                result += p.val;
                lastWasStatic = false;
            }
        });

        result = result.replace(/[\s\-\/\.]+$/, '');

        if (result) {
            callNumberInput.value = result;
            document.getElementById('auto-call-number-badge').classList.remove('hidden');
        } else {
            document.getElementById('auto-call-number-badge').classList.add('hidden');
        }
    }
</script>
@endsection
