@extends('layouts.app')

@php
    $associatedLibraryIds = Auth::user()->libraries()->pluck('libraries.id')->toArray();
@endphp

@section('title', 'Editar: ' . $fields['title'])

@section('content')
<div class="space-y-8">
    <!-- Back link -->
    <div>
        <a href="{{ route('catalog.show', $item->id) }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-400 transition duration-150">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Voltar para detalhes
        </a>
    </div>

    <!-- Header -->
    <div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-300">
            {{ Auth::user()->isCurator() ? 'Editar Metadados' : 'Sugerir Alteração de Metadados' }}
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            @if(Auth::user()->isCurator())
                As alterações feitas aqui serão publicadas diretamente no catálogo central.
            @else
                Sua sugestão de edição será enviada para a fila de moderação dos curadores.
            @endif
        </p>
    </div>

    <!-- Edit Form Grid -->
    <div class="bg-white border border-brand-border rounded-3xl p-6 md:p-8 shadow-xl">
        @if(Auth::user()->isCurator())
            <form id="form-delete-book" action="{{ route('catalog.destroy', $item->id) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif

        <form action="{{ route('catalog.update', $item->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-2xl">
                    <div class="text-sm font-bold mb-1">Por favor, corrija os seguintes erros:</div>
                    <ul class="list-disc list-inside text-xs space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Left Column: Cover Preview & Upload -->
                <div class="space-y-6">
                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-500">Capa do Livro</span>
                    
                    <!-- Preview Container -->
                    <div class="relative w-full aspect-[3/4] bg-slate-950 border border-brand-border rounded-2xl overflow-hidden flex items-center justify-center shadow-inner" id="cover-preview-container">
                        @if($fields['cover_url'])
                            <img id="cover-preview" src="{{ $fields['cover_url'] }}" alt="Capa atual" class="w-full h-full object-cover">
                        @else
                            <div id="cover-placeholder" class="text-slate-500 flex flex-col items-center justify-center p-4 text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mb-2 text-slate-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                </svg>
                                <span class="text-xs">Sem Imagem</span>
                            </div>
                        @endif
                        
                        <!-- Fetching Loader Overlay -->
                        <div id="fetch-loader" class="hidden absolute inset-0 bg-slate-950/80 flex flex-col items-center justify-center text-white text-xs font-semibold gap-2">
                            <svg class="animate-spin h-6 w-6 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Buscando capa...</span>
                        </div>
                    </div>

                    <!-- Fetch Button (Google / Open Library API by ISBN) -->
                    <div>
                        <button type="button" id="btn-fetch-cover" class="w-full bg-slate-950 hover:bg-slate-900 text-slate-300 border border-slate-800 rounded-xl py-2.5 text-xs font-semibold transition duration-150 flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            Buscar Capa na Web
                        </button>
                        <p class="text-[9px] text-slate-500 mt-1 text-center">Requer ISBN preenchido na lateral.</p>
                    </div>

                    <!-- Custom upload field -->
                    <div class="space-y-1.5">
                        <label for="cover_file" class="block text-xs uppercase font-bold text-slate-500">Subir Capa Personalizada</label>
                        <input type="file" id="cover_file" name="cover_file" accept="image/*"
                            class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary file:text-white hover:file:bg-primary-hover file:cursor-pointer">
                    </div>
                </div>

                <!-- Right Column: Meta Fields -->
                <div class="md:col-span-2 space-y-4">
                    <!-- Title field -->
                    <div class="space-y-1">
                        <label for="title" class="block text-xs uppercase font-bold text-slate-500">Título da Obra <span class="text-red-500">*</span></label>
                        <input type="text" id="title" name="title" value="{{ old('title', $fields['title']) }}" required
                            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition duration-200">
                        @error('title')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Author & Publisher -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label for="author" class="block text-xs uppercase font-bold text-slate-500">Autor / Responsabilidade <span class="text-red-500">*</span></label>
                            <input type="text" id="author" name="author" value="{{ old('author', $fields['author']) }}" required
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition duration-200">
                            @error('author')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-1">
                            <label for="publisher" class="block text-xs uppercase font-bold text-slate-500">Editora</label>
                            <input type="text" id="publisher" name="publisher" value="{{ old('publisher', $fields['publisher']) }}"
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition duration-200">
                        </div>
                    </div>

                    <!-- ISBN, Pub Year, Pages -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="space-y-1">
                            <label for="isbn" class="block text-xs uppercase font-bold text-slate-500">ISBN</label>
                            <input type="text" id="isbn" name="isbn" value="{{ old('isbn', $fields['isbn']) }}"
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition duration-200">
                        </div>
                        <div class="space-y-1">
                            <label for="publication_year" class="block text-xs uppercase font-bold text-slate-500">Ano Publicação</label>
                            <input type="text" id="publication_year" name="publication_year" value="{{ old('publication_year', $fields['publication_year']) }}" maxlength="4"
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition duration-200">
                        </div>
                        <div class="space-y-1">
                            <label for="pages" class="block text-xs uppercase font-bold text-slate-500">Nº de Páginas</label>
                            <input type="number" id="pages" name="pages" value="{{ old('pages', $fields['pages']) }}"
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition duration-200">
                        </div>
                    </div>

                    <!-- Hidden cover URL -->
                    <input type="hidden" id="cover_url" name="cover_url" value="{{ old('cover_url', $fields['cover_url']) }}">

                    <!-- Subjects (Tags) -->
                    <div class="space-y-1">
                        <label for="subjects" class="block text-xs uppercase font-bold text-slate-500">Assuntos / Tags (separados por vírgula)</label>
                        <input type="text" id="subjects" name="subjects" value="{{ old('subjects', $fields['subjects']) }}"
                            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-550 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition duration-200"
                            placeholder="ex: Literatura Brasileira, Realismo, Romance">
                    </div>

                    <!-- Description -->
                    <div class="space-y-1">
                        <label for="description" class="block text-xs uppercase font-bold text-slate-500">Sinopse / Resumo</label>
                        <textarea id="description" name="description" rows="5"
                            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition duration-200 resize-none"
                            placeholder="Insira o resumo ou sinopse da obra...">{{ old('description', $fields['description']) }}</textarea>
                    </div>

                </div>
            </div>

            <!-- Physical Copies Section (Fase 8) -->
            <div class="pt-6 border-t border-brand-border space-y-6">
                <h3 class="text-lg font-bold text-slate-500 flex items-center gap-2">
                    <span class="p-1.5 bg-primary/10 text-primary rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25s-7.5-4.108-7.5-11.25a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                    </span>
                    Exemplares Físicos da Obra
                </h3>

                <input type="hidden" name="deleted_copies" id="deleted-copies-input" value="">

                <!-- Existing Copies -->
                <div class="space-y-4">
                    <span class="block text-xs uppercase font-bold text-slate-500 tracking-wider">Unidades Cadastradas</span>
                    @if($localCopies->isEmpty())
                        <div class="text-slate-500 text-xs italic py-4 bg-slate-950 border border-brand-border rounded-2xl text-center">
                            Nenhum exemplar físico cadastrado em nenhuma biblioteca para esta obra.
                        </div>
                    @else
                        <div class="grid grid-cols-1 gap-4" id="copies-list">
                            @foreach($localCopies as $copy)
                                @php
                                    $isReadOnly = !Auth::user()->isCurator() && !in_array($copy->library_id, $associatedLibraryIds);
                                @endphp
                                <div class="p-4 bg-slate-950 border border-slate-800 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4" id="copy-card-{{ $copy->id }}">
                                    <div class="flex-1 space-y-3">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-semibold text-slate-200">{{ $copy->library->name }}</span>
                                            <input type="hidden" name="copies[{{ $copy->id }}][library_id]" value="{{ $copy->library_id }}">
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                                            <div class="space-y-1">
                                                <label class="block text-[10px] uppercase font-bold text-slate-500">Nº de Chamada</label>
                                                <input type="text" name="copies[{{ $copy->id }}][call_number]" value="{{ $copy->call_number }}"
                                                    {{ $isReadOnly ? 'readonly' : '' }}
                                                    class="w-full border border-slate-800 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:border-primary transition duration-150 {{ $isReadOnly ? 'bg-slate-900/40 text-slate-500 cursor-not-allowed' : 'bg-slate-900 text-slate-200' }}">
                                            </div>
                                            <div class="space-y-1">
                                                <label class="block text-[10px] uppercase font-bold text-slate-500">Estante / Prateleira</label>
                                                <input type="text" name="copies[{{ $copy->id }}][shelf_location]" value="{{ $copy->shelf_location }}"
                                                    {{ $isReadOnly ? 'readonly' : '' }}
                                                    class="w-full border border-slate-800 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:border-primary transition duration-150 {{ $isReadOnly ? 'bg-slate-900/40 text-slate-500 cursor-not-allowed' : 'bg-slate-900 text-slate-200' }}">
                                            </div>
                                            <div class="space-y-1">
                                                <label class="block text-[10px] uppercase font-bold text-slate-500">Quantidade</label>
                                                <input type="number" name="copies[{{ $copy->id }}][quantity]" value="{{ $copy->quantity }}" min="0" required
                                                    {{ $isReadOnly ? 'readonly' : '' }}
                                                    class="w-full border border-slate-800 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:border-primary transition duration-150 {{ $isReadOnly ? 'bg-slate-900/40 text-slate-500 cursor-not-allowed' : 'bg-slate-900 text-slate-200' }}">
                                            </div>
                                            <div class="space-y-1">
                                                <label class="block text-[10px] uppercase font-bold text-slate-500">Disponível para Empréstimo</label>
                                                @if($isReadOnly)
                                                    <!-- Read-only status for non-associated libraries -->
                                                    <div class="flex items-center h-[34px]">
                                                        <span class="text-xs font-semibold {{ $copy->status === 'unavailable' ? 'text-rose-500' : 'text-slate-500' }}">
                                                            {{ $copy->status === 'unavailable' ? 'Indisponível' : 'Disponível' }}
                                                        </span>
                                                        <input type="hidden" name="copies[{{ $copy->id }}][is_available]" value="{{ $copy->status !== 'unavailable' ? '1' : '0' }}">
                                                    </div>
                                                @else
                                                    <!-- Standard checkbox for associated libraries -->
                                                    <div class="flex items-center h-[34px] gap-2">
                                                        <input type="hidden" name="copies[{{ $copy->id }}][is_available]" value="0">
                                                        <label class="relative inline-flex items-center cursor-pointer select-none gap-2">
                                                            <input type="checkbox" name="copies[{{ $copy->id }}][is_available]" value="1" {{ $copy->status !== 'unavailable' ? 'checked' : '' }}
                                                                class="w-4 h-4 rounded text-primary accent-primary bg-slate-900 border-slate-800 focus:ring-primary focus:ring-2 focus:outline-none cursor-pointer">
                                                            <span class="text-xs font-medium text-slate-500">Disponível</span>
                                                        </label>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        @php
                                            $copyCustomFields = $copy->library ? $copy->library->getCustomFields() : [];
                                            $copyValues = is_array($copy->custom_fields_values) ? $copy->custom_fields_values : [];
                                        @endphp
                                        @if(!empty($copyCustomFields))
                                            <div class="pt-2.5 border-t border-slate-900 mt-2 space-y-2">
                                                <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider flex items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281Z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    </svg>
                                                    {{ __('Campos Específicos Desta Unidade') }}
                                                </span>
                                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                                    @foreach($copyCustomFields as $cf)
                                                        @php
                                                            $cfVal = $copyValues[$cf['key']] ?? '';
                                                        @endphp
                                                        <div class="space-y-1">
                                                            <label class="block text-[10px] text-slate-400 font-semibold">{{ $cf['label'] }}</label>
                                                            @if($cf['type'] === 'select')
                                                                <select name="copies[{{ $copy->id }}][custom_fields_values][{{ $cf['key'] }}]" {{ $isReadOnly ? 'disabled' : '' }} class="w-full border border-slate-800 rounded-lg px-2.5 py-1.5 text-xs bg-slate-900 text-slate-200 focus:outline-none focus:border-primary">
                                                                    <option value="">-- {{ __('Selecione') }} --</option>
                                                                    @foreach($cf['options'] ?? [] as $opt)
                                                                        <option value="{{ $opt }}" {{ (string)$cfVal === (string)$opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                                    @endforeach
                                                                </select>
                                                            @elseif($cf['type'] === 'boolean')
                                                                <div class="flex items-center h-[34px]">
                                                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                                                        <input type="checkbox" name="copies[{{ $copy->id }}][custom_fields_values][{{ $cf['key'] }}]" value="1" {{ !empty($cfVal) ? 'checked' : '' }} {{ $isReadOnly ? 'disabled' : '' }} class="rounded border-slate-800 bg-slate-900 text-primary">
                                                                        <span class="text-xs text-slate-300">{{ __('Sim') }}</span>
                                                                    </label>
                                                                </div>
                                                            @elseif($cf['type'] === 'integer')
                                                                @php
                                                                    $isAutoInc = !empty($cf['auto_increment']);
                                                                    $lowestAvailable = null;
                                                                    if ($isAutoInc && $copy->library) {
                                                                        $lowestAvailable = $copy->library->getLowestAvailableAutoIncrementValue($cf['key'], $copy->id);
                                                                        if ($cfVal === '' || $cfVal === null) {
                                                                            $cfVal = $lowestAvailable;
                                                                        }
                                                                    }
                                                                @endphp
                                                                <input type="number" id="copy_{{ $copy->id }}_{{ $cf['key'] }}" name="copies[{{ $copy->id }}][custom_fields_values][{{ $cf['key'] }}]" value="{{ $cfVal }}" {{ $isReadOnly ? 'readonly' : '' }} class="w-full border border-slate-800 rounded-lg px-2.5 py-1.5 text-xs bg-slate-900 text-slate-200 focus:outline-none focus:border-primary">
                                                                @if($isAutoInc && $lowestAvailable !== null && !$isReadOnly)
                                                                    <div class="flex items-center justify-between text-[9px] text-slate-400 mt-1">
                                                                        <span>{{ __('Menor disponível:') }} <strong class="text-emerald-400 font-mono">{{ $lowestAvailable }}</strong></span>
                                                                        @if((string)$cfVal !== (string)$lowestAvailable)
                                                                            <button type="button" onclick="document.getElementById('copy_{{ $copy->id }}_{{ $cf['key'] }}').value = '{{ $lowestAvailable }}'" class="text-primary hover:underline cursor-pointer">
                                                                                {{ __('Usar (:num)', ['num' => $lowestAvailable]) }}
                                                                            </button>
                                                                        @endif
                                                                    </div>
                                                                @endif
                                                            @else
                                                                <input type="text" name="copies[{{ $copy->id }}][custom_fields_values][{{ $cf['key'] }}]" value="{{ $cfVal }}" {{ $isReadOnly ? 'readonly' : '' }} class="w-full border border-slate-800 rounded-lg px-2.5 py-1.5 text-xs bg-slate-900 text-slate-200 focus:outline-none focus:border-primary">
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="sm:self-end">
                                        <button type="button" onclick="markCopyForDeletion({{ $copy->id }})" class="bg-red-950/40 text-red-400 border border-red-900/50 hover:bg-red-900/20 px-3.5 py-2 rounded-xl text-xs font-semibold transition duration-150">
                                            Remover
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Add New Copy Section -->
                <div class="p-5 bg-slate-950 border border-brand-border rounded-2xl space-y-4">
                    <span class="block text-xs uppercase font-bold text-slate-500 tracking-wider">Adicionar Exemplar a Outra Unidade</span>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div class="space-y-1.5 sm:col-span-2">
                            <label for="new_copy_library_id" class="block text-[10px] uppercase font-bold text-slate-500">Selecione a Biblioteca</label>
                            <select id="new_copy_library_id" name="new_copy_library_id"
                                class="w-full bg-slate-900 border border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-350 focus:outline-none focus:border-primary transition duration-150">
                                <option value="">-- Selecione uma biblioteca --</option>
                                @foreach($libraries as $lib)
                                    <option value="{{ $lib->id }}">{{ $lib->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label for="new_copy_quantity" class="block text-[10px] uppercase font-bold text-slate-500">Quantidade Inicial</label>
                            <input type="number" id="new_copy_quantity" name="new_copy_quantity" min="1" placeholder="ex: 1"
                                class="w-full bg-slate-900 border border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-primary transition duration-150">
                        </div>
                        <div class="space-y-1.5">
                            <label for="new_copy_call_number" class="block text-[10px] uppercase font-bold text-slate-500 flex items-center justify-between">
                                <span>Nº de Chamada</span>
                                <span id="new-copy-call-number-badge" class="hidden text-[9px] text-emerald-400 font-medium bg-emerald-500/10 px-1 rounded">✨ Automático</span>
                            </label>
                            <input type="text" id="new_copy_call_number" name="new_copy_call_number" placeholder="Opcional"
                                class="w-full bg-slate-900 border border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-primary transition duration-150">
                        </div>
                        <div class="space-y-1.5 sm:col-span-2">
                            <label for="new_copy_shelf_location" class="block text-[10px] uppercase font-bold text-slate-500">Estante / Prateleira</label>
                            <input type="text" id="new_copy_shelf_location" name="new_copy_shelf_location" placeholder="Opcional"
                                class="w-full bg-slate-900 border border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-primary transition duration-150">
                        </div>

                        <!-- Container para Campos Personalizados do Novo Exemplar -->
                        <div id="new-copy-custom-fields-wrapper" class="hidden sm:col-span-4 pt-3 border-t border-slate-850 space-y-2">
                            <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                {{ __('Campos Personalizados Desta Unidade') }}
                            </span>
                            <div id="new-copy-custom-fields-grid" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <!-- Preenchido dinamicamente via JS -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex items-center justify-between gap-4 pt-6 border-t border-brand-border mt-8">
                <div>
                    @if(Auth::user()->isCurator())
                        <div id="delete-zone" class="flex items-center gap-3">
                            <button type="button" id="btn-delete-step1" class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 font-semibold px-4 py-2.5 rounded-xl text-xs transition duration-150">
                                Excluir Livro
                            </button>
                            <div id="delete-confirmation-wrapper" class="hidden flex items-center gap-3">
                                <label class="inline-flex items-center gap-2 text-xs text-slate-600 cursor-pointer">
                                    <input type="checkbox" id="checkbox-delete-confirm" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                    <span>Confirmo a exclusão definitiva</span>
                                </label>
                                <button type="button" id="btn-delete-submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2.5 rounded-xl text-xs transition duration-150 opacity-50 cursor-not-allowed" disabled>
                                    Confirmar e Excluir
                                </button>
                                <button type="button" id="btn-delete-cancel" class="text-xs text-slate-400 hover:text-slate-600 transition duration-150">
                                    Cancelar
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('catalog.show', $item->id) }}" class="bg-white hover:bg-slate-950 text-slate-500 border border-brand-border font-semibold px-6 py-2.5 rounded-xl text-xs transition duration-150">
                        Cancelar
                    </a>
                    <button type="submit" class="bg-primary hover:bg-primary-hover text-white font-semibold px-8 py-2.5 rounded-xl text-xs transition duration-150 shadow-md">
                        {{ Auth::user()->isCurator() ? 'Salvar Edição' : 'Sugerir Alterações' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const isbnInput = document.getElementById('isbn');
        const coverUrlInput = document.getElementById('cover_url');
        const coverPreview = document.getElementById('cover-preview');
        const coverPreviewContainer = document.getElementById('cover-preview-container');
        const btnFetchCover = document.getElementById('btn-fetch-cover');
        const fetchLoader = document.getElementById('fetch-loader');
        
        // Handle local cover file upload selection to show immediate preview
        const coverFileInput = document.getElementById('cover_file');
        coverFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    let previewImg = document.getElementById('cover-preview');
                    if (!previewImg) {
                        // Create preview image if it was a placeholder
                        previewImg = document.createElement('img');
                        previewImg.id = 'cover-preview';
                        previewImg.className = 'w-full h-full object-cover';
                        coverPreviewContainer.innerHTML = '';
                        coverPreviewContainer.appendChild(previewImg);
                    }
                    previewImg.src = evt.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        // Handle Web Cover lookup via ISBN API lookup
        btnFetchCover.addEventListener('click', function() {
            const isbn = isbnInput.value.replace(/[^0-9]/g, '');
            if (!isbn) {
                alert('Por favor, informe o ISBN do livro para efetuar a busca da capa.');
                return;
            }

            fetchLoader.classList.remove('hidden');

            fetch(`/catalog/lookup?isbn=${isbn}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Capa não encontrada ou falha na consulta.');
                    }
                    return response.json();
                })
                .then(data => {
                    fetchLoader.classList.add('hidden');
                    if (data.cover_url) {
                        coverUrlInput.value = data.cover_url;
                        let previewImg = document.getElementById('cover-preview');
                        if (!previewImg) {
                            previewImg = document.createElement('img');
                            previewImg.id = 'cover-preview';
                            previewImg.className = 'w-full h-full object-cover';
                            coverPreviewContainer.innerHTML = '';
                            coverPreviewContainer.appendChild(previewImg);
                        }
                        previewImg.src = data.cover_url;
                        
                        // Copy other empty fields to help catalogers
                        if (!document.getElementById('title').value && data.title) {
                            document.getElementById('title').value = data.title;
                        }
                        if (!document.getElementById('author').value && data.author) {
                            document.getElementById('author').value = data.author;
                        }
                        if (!document.getElementById('publisher').value && data.publisher) {
                            document.getElementById('publisher').value = data.publisher;
                        }
                        if (!document.getElementById('publication_year').value && data.publication_year) {
                            document.getElementById('publication_year').value = data.publication_year;
                        }
                        if (!document.getElementById('pages').value && data.pages) {
                            document.getElementById('pages').value = data.pages;
                        }
                        if (!document.getElementById('description').value && data.description) {
                            document.getElementById('description').value = data.description;
                        }
                        if (!document.getElementById('subjects').value && data.subjects && data.subjects.length > 0) {
                            document.getElementById('subjects').value = data.subjects.join(', ');
                        }
                    } else {
                        alert('Nenhuma imagem de capa foi localizada para este ISBN nas bases de dados externas.');
                    }
                })
                .catch(err => {
                    fetchLoader.classList.add('hidden');
                    alert(err.message || 'Erro ao consultar o ISBN.');
                });
        });

        // Delete flow for Curators/Admins
        const btnDeleteStep1 = document.getElementById('btn-delete-step1');
        const deleteConfirmationWrapper = document.getElementById('delete-confirmation-wrapper');
        const checkboxDeleteConfirm = document.getElementById('checkbox-delete-confirm');
        const btnDeleteSubmit = document.getElementById('btn-delete-submit');
        const btnDeleteCancel = document.getElementById('btn-delete-cancel');
        const formDeleteBook = document.getElementById('form-delete-book');

        if (btnDeleteStep1) {
            btnDeleteStep1.addEventListener('click', function() {
                btnDeleteStep1.classList.add('hidden');
                deleteConfirmationWrapper.classList.remove('hidden');
                checkboxDeleteConfirm.checked = false;
                updateDeleteSubmitButton();
            });

            checkboxDeleteConfirm.addEventListener('change', function() {
                updateDeleteSubmitButton();
            });

            btnDeleteCancel.addEventListener('click', function() {
                deleteConfirmationWrapper.classList.add('hidden');
                btnDeleteStep1.classList.remove('hidden');
                checkboxDeleteConfirm.checked = false;
            });

            btnDeleteSubmit.addEventListener('click', function() {
                if (checkboxDeleteConfirm.checked && !btnDeleteSubmit.disabled) {
                    formDeleteBook.submit();
                }
            });

            function updateDeleteSubmitButton() {
                if (checkboxDeleteConfirm.checked) {
                    btnDeleteSubmit.disabled = false;
                    btnDeleteSubmit.classList.remove('opacity-50', 'cursor-not-allowed');
                } else {
                    btnDeleteSubmit.disabled = true;
                    btnDeleteSubmit.classList.add('opacity-50', 'cursor-not-allowed');
                }
            }
        }

        // Mark physical copy for deletion
        const deletedCopiesInput = document.getElementById('deleted-copies-input');
        window.markCopyForDeletion = function(copyId) {
            if (confirm('Deseja realmente remover esta cópia física? O exemplar será desvinculado ao salvar o formulário.')) {
                const card = document.getElementById('copy-card-' + copyId);
                if (card) {
                    card.classList.add('hidden');
                }
                let currentVal = deletedCopiesInput.value;
                let currentArr = currentVal ? currentVal.split(',') : [];
                if (!currentArr.includes(String(copyId))) {
                    currentArr.push(String(copyId));
                }
                deletedCopiesInput.value = currentArr.join(',');
            }
        };

        // --- GERENCIAMENTO DE CAMPOS PERSONALIZADOS DO NOVO EXEMPLAR ---
        const newCopyLibSelect = document.getElementById('new_copy_library_id');
        const newCopyCallNumberInput = document.getElementById('new_copy_call_number');
        const newCopyBadge = document.getElementById('new-copy-call-number-badge');
        let newCopyLibConfig = null;
        let newCopyCustomValues = {};
        let isNewCopyCallNumberEdited = false;

        if (newCopyLibSelect) {
            newCopyLibSelect.addEventListener('change', function() {
                isNewCopyCallNumberEdited = false;
                const libId = this.value;
                if (!libId) {
                    document.getElementById('new-copy-custom-fields-wrapper').classList.add('hidden');
                    if (newCopyBadge) newCopyBadge.classList.add('hidden');
                    return;
                }

                fetch(`/libraries/${libId}/custom-fields`)
                    .then(r => r.json())
                    .then(data => {
                        newCopyLibConfig = data;
                        renderNewCopyCustomFields(data.custom_fields || []);
                        calculateNewCopyCallNumber();
                    })
                    .catch(e => console.error('Erro ao buscar campos da biblioteca:', e));
            });
        }

        if (newCopyCallNumberInput) {
            newCopyCallNumberInput.addEventListener('input', function() {
                isNewCopyCallNumberEdited = true;
                if (!this.value) {
                    isNewCopyCallNumberEdited = false;
                    calculateNewCopyCallNumber();
                }
            });
        }

        function renderNewCopyCustomFields(fields) {
            const wrapper = document.getElementById('new-copy-custom-fields-wrapper');
            const grid = document.getElementById('new-copy-custom-fields-grid');
            grid.innerHTML = '';
            newCopyCustomValues = {};

            if (!fields || fields.length === 0) {
                wrapper.classList.add('hidden');
                return;
            }

            wrapper.classList.remove('hidden');
            fields.forEach(f => {
                const col = document.createElement('div');
                col.className = 'space-y-1';

                const label = document.createElement('label');
                label.className = 'block text-[10px] text-slate-400 font-semibold';
                label.textContent = f.label;
                col.appendChild(label);

                if (f.type === 'select') {
                    const sel = document.createElement('select');
                    sel.name = `new_copy_custom_fields_values[${f.key}]`;
                    sel.className = 'w-full bg-slate-900 border border-slate-800 rounded-lg px-2.5 py-1.5 text-xs text-slate-200 focus:outline-none focus:border-primary';
                    const defOpt = document.createElement('option');
                    defOpt.value = '';
                    defOpt.textContent = '-- Selecione --';
                    sel.appendChild(defOpt);

                    (f.options || []).forEach(opt => {
                        const o = document.createElement('option');
                        o.value = opt;
                        o.textContent = opt;
                        sel.appendChild(o);
                    });

                    sel.addEventListener('change', () => {
                        newCopyCustomValues[f.key] = sel.value;
                        calculateNewCopyCallNumber();
                    });
                    col.appendChild(sel);
                } else if (f.type === 'boolean') {
                    const wrap = document.createElement('div');
                    wrap.className = 'flex items-center h-[34px]';
                    const lbl = document.createElement('label');
                    lbl.className = 'inline-flex items-center gap-2 cursor-pointer';
                    const cb = document.createElement('input');
                    cb.type = 'checkbox';
                    cb.name = `new_copy_custom_fields_values[${f.key}]`;
                    cb.value = '1';
                    cb.className = 'rounded border-slate-800 bg-slate-900 text-primary';
                    cb.addEventListener('change', () => {
                        newCopyCustomValues[f.key] = cb.checked ? '1' : '0';
                        calculateNewCopyCallNumber();
                    });
                    const sp = document.createElement('span');
                    sp.className = 'text-xs text-slate-300';
                    sp.textContent = 'Sim';
                    lbl.appendChild(cb);
                    lbl.appendChild(sp);
                    wrap.appendChild(lbl);
                    col.appendChild(wrap);
                } else if (f.type === 'integer') {
                    const inp = document.createElement('input');
                    inp.type = 'number';
                    inp.name = `new_copy_custom_fields_values[${f.key}]`;
                    inp.className = 'w-full bg-slate-900 border border-slate-800 rounded-lg px-2.5 py-1.5 text-xs text-slate-200 focus:outline-none focus:border-primary';
                    if (f.auto_increment && f.suggested_value) {
                        inp.value = f.suggested_value;
                        newCopyCustomValues[f.key] = f.suggested_value;
                        inp.placeholder = `Sugerido: ${f.suggested_value}`;
                    } else if (f.auto_increment) {
                        inp.placeholder = 'Automático (sequencial)';
                    }
                    inp.addEventListener('input', () => {
                        newCopyCustomValues[f.key] = inp.value;
                        calculateNewCopyCallNumber();
                    });
                    col.appendChild(inp);
                    if (f.auto_increment && f.suggested_value) {
                        const hint = document.createElement('div');
                        hint.className = 'text-[9px] text-slate-400 mt-0.5';
                        hint.innerHTML = `Menor disponível: <strong class="text-emerald-400 font-mono">${f.suggested_value}</strong>`;
                        col.appendChild(hint);
                    }
                } else {
                    const inp = document.createElement('input');
                    inp.type = 'text';
                    inp.name = `new_copy_custom_fields_values[${f.key}]`;
                    inp.className = 'w-full bg-slate-900 border border-slate-800 rounded-lg px-2.5 py-1.5 text-xs text-slate-200 focus:outline-none focus:border-primary';
                    inp.addEventListener('input', () => {
                        newCopyCustomValues[f.key] = inp.value;
                        calculateNewCopyCallNumber();
                    });
                    col.appendChild(inp);
                }

                grid.appendChild(col);
            });
        }

        function calculateNewCopyCallNumber() {
            if (!newCopyLibConfig || !newCopyLibConfig.has_call_number_format) {
                if (newCopyBadge) newCopyBadge.classList.add('hidden');
                return;
            }

            const format = newCopyLibConfig.call_number_format;
            if (!format || format.length === 0) {
                if (newCopyBadge) newCopyBadge.classList.add('hidden');
                return;
            }

            if (isNewCopyCallNumberEdited && newCopyCallNumberInput.value.trim() !== '') {
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
                        raw = newCopyCustomValues[el.key] || '';
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
                newCopyCallNumberInput.value = result;
                if (newCopyBadge) newCopyBadge.classList.remove('hidden');
            } else {
                if (newCopyBadge) newCopyBadge.classList.add('hidden');
            }
        }
    });
</script>
@endsection
