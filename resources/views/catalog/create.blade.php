@extends('layouts.app')

@section('title', 'Catalogar Livro')

@section('content')
<div class="space-y-8">
    <!-- Back Link & Header -->
    <div>
        <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-slate-200 transition duration-150">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Voltar ao catálogo
        </a>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-100">Catalogar Novo Livro</h1>
            <p class="text-sm text-slate-400 mt-1">Insira o ISBN para buscar automaticamente os metadados bibliográficos ou preencha manualmente.</p>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column: Search & Camera / Preview -->
        <div class="space-y-6">
            <!-- ISBN Search Box -->
            <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 shadow-xl space-y-4">
                <h2 class="text-lg font-bold text-slate-200">Busca Rápida por Código</h2>
                
                <div class="space-y-3">
                    <div class="relative">
                        <input type="text" id="isbn_search" placeholder="Digite o ISBN (ex: 9788535902778)..." 
                            class="w-full bg-slate-900/50 border border-slate-800 rounded-xl pl-4 pr-12 py-3 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-200">
                        <button type="button" onclick="performLookup()" class="absolute right-2 top-2 p-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-xs font-semibold transition duration-150">
                            Buscar
                        </button>
                    </div>

                    <div class="flex gap-2">
                        <button type="button" id="start-btn" onclick="startScanner()" class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700/80 font-medium py-2.5 rounded-xl text-xs transition duration-150 flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-blue-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 47.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                            </svg>
                            Usar Câmera (Scanner)
                        </button>
                        <button type="button" id="stop-btn" onclick="stopScanner()" style="display: none;" class="flex-1 bg-red-950/40 text-red-400 border border-red-900/50 hover:bg-red-900/20 font-medium py-2.5 rounded-xl text-xs transition duration-150 flex items-center justify-center gap-2">
                            Parar Câmera
                        </button>
                    </div>
                </div>

                <!-- Camera Scanner Box -->
                <div id="reader-container" class="hidden relative mt-4 overflow-hidden rounded-2xl border border-slate-800 bg-slate-950 flex flex-col items-center">
                    <div id="reader" class="w-full aspect-square"></div>
                    <div class="p-3 bg-slate-900 w-full text-center text-[10px] text-slate-500 border-t border-slate-800">
                        Aponte a câmera traseira do celular para o código de barras (EAN-13/ISBN).
                    </div>
                </div>

                <!-- Loading Spinner Box -->
                <div id="loading-box" style="display: none;" class="py-12 flex flex-col items-center justify-center bg-slate-900/20 rounded-2xl border border-slate-850">
                    <div class="w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                    <span class="text-xs text-slate-400 mt-3 font-medium">Buscando metadados...</span>
                </div>
            </div>

            <!-- Cover Preview Box -->
            <div id="cover-preview-box" class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 shadow-xl flex flex-col items-center justify-center text-center space-y-4">
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500 w-full text-left">Pré-visualização da Capa</span>
                
                <div class="w-48 aspect-[3/4] bg-slate-950 rounded-2xl border border-slate-800/80 overflow-hidden flex items-center justify-center relative">
                    <img id="cover_preview" src="" alt="Capa" class="w-full h-full object-cover hidden">
                    <div id="cover_placeholder" class="text-slate-700 flex flex-col items-center justify-center p-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-16 h-16 mb-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                        </svg>
                        <span class="text-[10px] uppercase tracking-wide">Sem imagem da capa</span>
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
                        Metadados Bibliográficos (Central)
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Title -->
                        <div class="md:col-span-2 space-y-1.5">
                            <label for="title" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">Título <span class="text-red-500">*</span></label>
                            <input type="text" id="title" name="title" required value="{{ old('title') }}"
                                class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                            @error('title') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Author -->
                        <div class="space-y-1.5">
                            <label for="author" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">Autor / Criador</label>
                            <input type="text" id="author" name="author" value="{{ old('author') }}"
                                class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                        </div>

                        <!-- ISBN -->
                        <div class="space-y-1.5">
                            <label for="isbn" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">ISBN</label>
                            <input type="text" id="isbn" name="isbn" value="{{ old('isbn') }}"
                                class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                        </div>

                        <!-- Publisher -->
                        <div class="space-y-1.5">
                            <label for="publisher" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">Editora</label>
                            <input type="text" id="publisher" name="publisher" value="{{ old('publisher') }}"
                                class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                        </div>

                        <!-- Publication Year & Pages -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label for="publication_year" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">Ano</label>
                                <input type="text" id="publication_year" name="publication_year" maxlength="4" value="{{ old('publication_year') }}"
                                    class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                            </div>
                            <div class="space-y-1.5">
                                <label for="pages" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">Páginas</label>
                                <input type="number" id="pages" name="pages" value="{{ old('pages') }}"
                                    class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                            </div>
                        </div>

                        <!-- Subjects / Tags -->
                        <div class="md:col-span-2 space-y-1.5">
                            <label for="subjects" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">Assuntos / Tags <span class="text-[10px] text-slate-500 lowercase normal-case">(separados por vírgula)</span></label>
                            <input type="text" id="subjects" name="subjects" value="{{ old('subjects') }}"
                                placeholder="Literatura Brasileira, Romance, História, etc."
                                class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2 space-y-1.5">
                            <label for="description" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">Sinopse / Descrição</label>
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
                        Dados do Exemplar Físico (Local)
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <!-- Call Number -->
                        <div class="space-y-1.5">
                            <label for="call_number" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">Nº de Chamada</label>
                            <input type="text" id="call_number" name="call_number" placeholder="ex: 869 M149d" value="{{ old('call_number') }}"
                                class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                        </div>

                        <!-- Shelf Location -->
                        <div class="space-y-1.5">
                            <label for="shelf_location" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">Estante / Prateleira</label>
                            <input type="text" id="shelf_location" name="shelf_location" placeholder="ex: Corredor A, Estante 3" value="{{ old('shelf_location') }}"
                                class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                        </div>

                        <!-- Quantity -->
                        <div class="space-y-1.5">
                            <label for="quantity" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">Quantidade <span class="text-red-500">*</span></label>
                            <input type="number" id="quantity" name="quantity" required min="1" value="{{ old('quantity', 1) }}"
                                class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition duration-150">
                            @error('quantity') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('catalog.index') }}" class="bg-slate-900 hover:bg-slate-800 text-slate-400 border border-slate-800 font-medium px-6 py-3 rounded-xl text-sm transition duration-150">
                        Cancelar
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-medium px-8 py-3 rounded-xl text-sm transition duration-150 shadow-lg shadow-blue-500/15">
                        Confirmar e Salvar no Acervo
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

        html5QrCode = new Html5Qrcode("reader");
        
        html5QrCode.start(
            { facingMode: "environment" },
            {
                fps: 15,
                qrbox: function(width, height) {
                    // Retângulo perfeito para código de barras EAN-13
                    const boxWidth = Math.min(width * 0.85, 320);
                    const boxHeight = Math.min(height * 0.4, 160);
                    return { width: boxWidth, height: boxHeight };
                },
                formatsToSupport: [
                    Html5QrcodeSupportedFormats.EAN_13,
                    Html5QrcodeSupportedFormats.EAN_8,
                    Html5QrcodeSupportedFormats.UPC_A,
                    Html5QrcodeSupportedFormats.UPC_E,
                    Html5QrcodeSupportedFormats.CODE_128
                ]
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
            alert("Não foi possível acessar a câmera. Verifique se deu permissão e se está em uma página HTTPS.");
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
            alert('Por favor, digite um ISBN válido.');
            return;
        }
        lookupIsbn(val);
    }

    function lookupIsbn(isbn) {
        // Exibe o loading e limpa preview da capa
        const loadingBox = document.getElementById('loading-box');
        loadingBox.style.display = 'flex';

        fetch(`/catalog/lookup?isbn=${encodeURIComponent(isbn)}`)
            .then(res => {
                if (!res.ok) {
                    throw new Error('Livro não localizado nas bases públicas');
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
                alert('Erro: Não conseguimos resgatar dados automaticamente para este ISBN. Você pode cadastrar os metadados manualmente.');
                console.error(err);
            });
    }
</script>
@endsection
