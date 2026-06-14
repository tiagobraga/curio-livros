@extends('layouts.app')

@section('title', 'Novo Empréstimo')

@section('content')
<div class="space-y-8">
    <!-- Back Link -->
    <div>
        <a href="{{ route('loans.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-slate-200 transition duration-150">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Voltar para empréstimos
        </a>
    </div>

    <!-- Header -->
    <div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-100">Registrar Saída de Livro</h1>
        <p class="text-sm text-slate-400 mt-1">Selecione o leitor, a obra disponível na sua unidade e determine o prazo de devolução.</p>
    </div>

    <!-- Form Container -->
    <div class="max-w-2xl bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 md:p-8 shadow-xl">
        <form action="{{ route('loans.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Select Reader -->
            <div class="space-y-2">
                <label for="user_id" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">Leitor (Usuário) <span class="text-red-500">*</span></label>
                <select id="user_id" name="user_id" required
                    class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-200">
                    <option value="" disabled selected>Escolha o leitor...</option>
                    @foreach($readers as $reader)
                        <option value="{{ $reader->id }}" {{ old('user_id') == $reader->id ? 'selected' : '' }}>
                            {{ $reader->name }} ({{ $reader->email }})
                        </option>
                    @endforeach
                </select>
                @error('user_id')
                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Select Book / Local Copy -->
            <div class="space-y-2">
                <label for="local_copy_id" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">Livro / Exemplar Disponível <span class="text-red-500">*</span></label>
                <select id="local_copy_id" name="local_copy_id" required
                    class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-200">
                    <option value="" disabled selected>Escolha o exemplar físico...</option>
                    @foreach($availableCopies as $copy)
                        <option value="{{ $copy->id }}" {{ old('local_copy_id') == $copy->id ? 'selected' : '' }}>
                            {{ $copy->centralItem->title }} 
                            @if($copy->call_number) — Chamada: {{ $copy->call_number }} @endif
                            @if($copy->shelf_location) [Estante: {{ $copy->shelf_location }}] @endif
                            (ISBN: {{ $copy->centralItem->isbn ?? 'Sem ISBN' }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[10px] text-slate-500">Apenas obras com saldo físico maior do que empréstimos ativos em sua unidade são listadas acima.</p>
                @error('local_copy_id')
                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Due Date -->
            <div class="space-y-2">
                <label for="due_date" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">Data de Devolução (Limite) <span class="text-red-500">*</span></label>
                <input type="date" id="due_date" name="due_date" required min="{{ date('Y-m-d') }}" value="{{ old('due_date', $defaultDueDate) }}"
                    class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-200">
                @error('due_date')
                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action buttons -->
            <div class="flex items-center justify-end gap-4 pt-4 border-t border-slate-850">
                <a href="{{ route('loans.index') }}" class="bg-slate-900 hover:bg-slate-800 text-slate-400 border border-slate-800 font-medium px-6 py-2.5 rounded-xl text-xs transition duration-150">
                    Cancelar
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-medium px-8 py-2.5 rounded-xl text-xs transition duration-150 shadow-lg shadow-blue-500/15">
                    Registrar Saída
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
