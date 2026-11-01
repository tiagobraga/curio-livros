@extends('layouts.app')

@section('title', 'Novo Empréstimo')

@section('content')
<div class="space-y-8">
    <!-- Back Link -->
    <div>
        <a href="{{ route('loans.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-450 transition duration-150">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Voltar para empréstimos
        </a>
    </div>

    <!-- Header -->
    <div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-300">Registrar Saída de Livro</h1>
        <p class="text-sm text-slate-500 mt-1">Selecione o leitor, a obra disponível na sua unidade e determine o prazo de devolução.</p>
    </div>

    <!-- Form Container -->
    <div class="max-w-2xl bg-white border border-brand-border rounded-3xl p-6 md:p-8 shadow-xl">
        <form action="{{ route('loans.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Autocomplete Reader -->
            <div class="space-y-2 relative" id="autocomplete-reader-container">
                <label for="reader_search" class="block text-xs uppercase font-bold text-slate-500 tracking-wider">Leitor (Usuário) <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="text" id="reader_search" placeholder="Comece a digitar o nome ou e-mail..." autocomplete="off"
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-10 pr-4 py-3 text-sm text-slate-100 placeholder-slate-400 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="absolute left-3.5 top-3.5 w-4 h-4 text-slate-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <input type="hidden" id="user_id" name="user_id" required value="{{ old('user_id') }}">

                <!-- Dropdown list -->
                <div id="reader_dropdown" class="hidden absolute left-0 right-0 mt-1 max-h-60 overflow-y-auto bg-white border border-brand-border rounded-xl shadow-xl z-30 divide-y divide-brand-border/40">
                    <!-- populated dynamically via JS -->
                </div>
                @error('user_id')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Autocomplete Book / Local Copy -->
            <div class="space-y-2 relative" id="autocomplete-book-container">
                <label for="book_search" class="block text-xs uppercase font-bold text-slate-500 tracking-wider">Livro / Exemplar Disponível <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="text" id="book_search" placeholder="Comece a digitar o título, chamada ou ISBN..." autocomplete="off"
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-10 pr-4 py-3 text-sm text-slate-100 placeholder-slate-400 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="absolute left-3.5 top-3.5 w-4 h-4 text-slate-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <input type="hidden" id="local_copy_id" name="local_copy_id" required value="{{ old('local_copy_id') }}">

                <!-- Dropdown list -->
                <div id="book_dropdown" class="hidden absolute left-0 right-0 mt-1 max-h-60 overflow-y-auto bg-white border border-brand-border rounded-xl shadow-xl z-30 divide-y divide-brand-border/40">
                    <!-- populated dynamically via JS -->
                </div>
                <p class="text-[10px] text-slate-500">Apenas obras com saldo físico maior do que empréstimos ativos em sua unidade são listadas acima.</p>
                @error('local_copy_id')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Due Date -->
            <div class="space-y-2">
                <label for="due_date" class="block text-xs uppercase font-bold text-slate-500 tracking-wider">Data de Devolução (Limite) <span class="text-red-500">*</span></label>
                <input type="date" id="due_date" name="due_date" required min="{{ date('Y-m-d') }}" value="{{ old('due_date', $defaultDueDate) }}"
                    class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition duration-200">
                @error('due_date')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action buttons -->
            <div class="flex items-center justify-end gap-4 pt-4 border-t border-brand-border">
                <a href="{{ route('loans.index') }}" class="bg-white hover:bg-slate-950 text-slate-500 border border-brand-border font-semibold px-6 py-2.5 rounded-xl text-xs transition duration-150">
                    Cancelar
                </a>
                <button type="submit" class="bg-primary hover:bg-primary-hover text-white font-semibold px-8 py-2.5 rounded-xl text-xs transition duration-150 shadow-md">
                    Registrar Saída
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- DATA INJECTION ---
        const readers = @json($readers);
        const copies = [
            @foreach($availableCopies as $copy)
            {
                id: {{ $copy->id }},
                title: "{{ addslashes($copy->centralItem->title) }}",
                call_number: "{{ addslashes($copy->call_number ?? '') }}",
                shelf_location: "{{ addslashes($copy->shelf_location ?? '') }}",
                isbn: "{{ $copy->centralItem->isbn ?? '' }}",
                library_name: "{{ addslashes($copy->library->name ?? '') }}"
            },
            @endforeach
        ];

        // --- DOM ELEMENTS ---
        const readerSearchInput = document.getElementById('reader_search');
        const readerHiddenInput = document.getElementById('user_id');
        const readerDropdown = document.getElementById('reader_dropdown');

        const bookSearchInput = document.getElementById('book_search');
        const bookHiddenInput = document.getElementById('local_copy_id');
        const bookDropdown = document.getElementById('book_dropdown');

        // --- REFILL OLD VALUES ---
        const oldUserId = "{{ old('user_id') }}";
        if (oldUserId) {
            const selectedReader = readers.find(r => r.id == oldUserId);
            if (selectedReader) {
                readerSearchInput.value = `${selectedReader.name} (${selectedReader.email})`;
                readerHiddenInput.value = oldUserId;
            }
        }

        const oldCopyId = "{{ old('local_copy_id') }}";
        if (oldCopyId) {
            const selectedBook = copies.find(c => c.id == oldCopyId);
            if (selectedBook) {
                bookSearchInput.value = selectedBook.title + (selectedBook.call_number ? ` (Chamada: ${selectedBook.call_number})` : '');
                bookHiddenInput.value = oldCopyId;
            }
        }

        // --- AUTOCOMPLETE: READERS ---
        readerSearchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            readerDropdown.innerHTML = '';
            readerHiddenInput.value = ''; // Reset ID if user is typing

            if (query.length < 2) {
                readerDropdown.classList.add('hidden');
                return;
            }

            const filtered = readers.filter(r => 
                r.name.toLowerCase().includes(query) || 
                r.email.toLowerCase().includes(query)
            ).slice(0, 10);

            if (filtered.length === 0) {
                readerDropdown.innerHTML = '<div class="px-4 py-3 text-sm text-slate-500">Nenhum leitor encontrado</div>';
                readerDropdown.classList.remove('hidden');
                return;
            }

            filtered.forEach(reader => {
                const item = document.createElement('div');
                item.className = 'px-4 py-3 text-sm text-slate-400 hover:bg-slate-950 hover:text-primary cursor-pointer transition duration-150';
                item.innerHTML = `<span class="font-semibold text-slate-200">${reader.name}</span> <span class="text-xs text-slate-500">(${reader.email})</span>`;
                item.addEventListener('click', function() {
                    readerSearchInput.value = `${reader.name} (${reader.email})`;
                    readerHiddenInput.value = reader.id;
                    readerDropdown.classList.add('hidden');
                });
                readerDropdown.appendChild(item);
            });

            readerDropdown.classList.remove('hidden');
        });

        // --- AUTOCOMPLETE: BOOKS ---
        bookSearchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            bookDropdown.innerHTML = '';
            bookHiddenInput.value = ''; // Reset ID if user is typing

            if (query.length < 2) {
                bookDropdown.classList.add('hidden');
                return;
            }

            const filtered = copies.filter(c => 
                c.title.toLowerCase().includes(query) || 
                c.isbn.includes(query) || 
                c.call_number.toLowerCase().includes(query)
            ).slice(0, 10);

            if (filtered.length === 0) {
                bookDropdown.innerHTML = '<div class="px-4 py-3 text-sm text-slate-500">Nenhum exemplar disponível localizado</div>';
                bookDropdown.classList.remove('hidden');
                return;
            }

            filtered.forEach(copy => {
                const item = document.createElement('div');
                item.className = 'px-4 py-3 text-sm text-slate-400 hover:bg-slate-950 hover:text-primary cursor-pointer transition duration-150';
                
                let details = [];
                if (copy.library_name) details.push(`Unidade: ${copy.library_name}`);
                if (copy.call_number) details.push(`Chamada: ${copy.call_number}`);
                if (copy.shelf_location) details.push(`Estante: ${copy.shelf_location}`);
                if (copy.isbn) details.push(`ISBN: ${copy.isbn}`);
                const detailsStr = details.length > 0 ? ` — <span class="text-xs text-slate-500">${details.join(', ')}</span>` : '';

                item.innerHTML = `<span class="font-semibold text-slate-200">${copy.title}</span>${detailsStr}`;
                item.addEventListener('click', function() {
                    bookSearchInput.value = copy.title + (copy.library_name ? ` [${copy.library_name}]` : '') + (copy.call_number ? ` (Chamada: ${copy.call_number})` : '');
                    bookHiddenInput.value = copy.id;
                    bookDropdown.classList.add('hidden');
                });
                bookDropdown.appendChild(item);
            });

            bookDropdown.classList.remove('hidden');
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!document.getElementById('autocomplete-reader-container').contains(e.target)) {
                readerDropdown.classList.add('hidden');
            }
            if (!document.getElementById('autocomplete-book-container').contains(e.target)) {
                bookDropdown.classList.add('hidden');
            }
        });
    });
</script>
@endsection
