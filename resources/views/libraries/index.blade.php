@extends('layouts.app')

@section('title', __('Gerenciamento de Unidades'))

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-100">{{ __('Gerenciamento de Unidades') }}</h1>
            <p class="text-sm text-slate-400 mt-1">{{ __('Cadastre e gerencie as unidades físicas da rede de bibliotecas Curió.') }}</p>
        </div>
        
        <a href="{{ route('libraries.create') }}" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white font-medium px-4 py-2.5 rounded-xl text-sm transition duration-150 shadow-lg shadow-emerald-950/15">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.75-7.75h-15" />
            </svg>
            {{ __('Nova Unidade') }}
        </a>
    </div>

    <!-- Table -->
    <div class="bg-slate-900/30 backdrop-blur border border-slate-800/80 rounded-3xl p-6 shadow-xl">
        @if($libraries->isEmpty())
            <div class="text-center py-16">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-16 h-16 text-slate-700 mx-auto mb-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25s-7.5-4.108-7.5-11.25a7.5 7.5 0 1 1 15 0Z" />
                </svg>
                <h3 class="text-lg font-semibold text-slate-300">{{ __('Nenhuma unidade cadastrada') }}</h3>
                <p class="text-sm text-slate-500 mt-1">{{ __('Clique no botão acima para cadastrar a primeira biblioteca.') }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-850 text-slate-400 text-xs uppercase tracking-wider font-bold">
                            <th class="py-4 px-4">{{ __('Código (ID)') }}</th>
                            <th class="py-4 px-4">{{ __('Nome da Biblioteca / Unidade') }}</th>
                            <th class="py-4 px-4">{{ __('Status') }}</th>
                            <th class="py-4 px-4">{{ __('Data Cadastro') }}</th>
                            <th class="py-4 px-4 text-right">{{ __('Ações') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-850/50 text-sm">
                        @foreach($libraries as $lib)
                            <tr class="hover:bg-slate-900/10 transition duration-150">
                                <td class="py-4 px-4 text-slate-400 font-mono">#{{ $lib->id }}</td>
                                <td class="py-4 px-4 font-semibold text-slate-200">
                                    {{ $lib->name }}
                                </td>
                                <td class="py-4 px-4">
                                    @if($lib->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/15">
                                            {{ __('Ativa') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-red-500/10 text-red-400 border border-red-500/15">
                                            {{ __('Inativa') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-slate-400">{{ $lib->created_at->format('d/m/Y') }}</td>
                                <td class="py-4 px-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('libraries.edit', $lib->id) }}" 
                                            class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 px-3 py-1.5 rounded-lg text-xs font-semibold transition duration-150">
                                            {{ __('Editar') }}
                                        </a>

                                        <form action="{{ route('libraries.destroy', $lib->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('{{ __('Deseja realmente excluir esta biblioteca? A exclusão só será permitida se não houverem exemplares físicos associados.') }}')" 
                                                class="bg-red-950/40 text-red-400 border border-red-900/50 hover:bg-red-900/20 px-3 py-1.5 rounded-lg text-xs font-semibold transition duration-150">
                                                {{ __('Excluir') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $libraries->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
