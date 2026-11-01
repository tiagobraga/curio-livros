<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha — Biblió</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased min-h-screen flex flex-col justify-between">

    <!-- Top Green Bar -->
    <header class="bg-primary h-4 shadow-sm w-full flex-shrink-0"></header>

    <main class="flex-grow flex items-center justify-center p-4 md:p-8">
        <div class="w-full max-w-md">
            
            <div class="text-center mb-6">
                <img src="/images/biblio_logo.png" alt="Logo Biblió" class="h-16 mx-auto mb-4 object-contain">
                <h1 class="text-2xl font-bold text-slate-800">Recuperação de Senha</h1>
                <p class="text-sm text-slate-500 mt-1">Informe seu e-mail cadastrado para gerar uma nova senha</p>
            </div>

            <div class="bg-white border border-slate-200 rounded-3xl p-8 shadow-xl">
                @if (session('error'))
                    <div class="mb-4 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs md:text-sm font-medium">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs md:text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('password.email') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">E-mail Cadastrado</label>
                        <input type="email" name="email" id="email" required autofocus placeholder="seu.email@exemplo.com" class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-primary focus:border-primary transition text-sm">
                    </div>

                    <button type="submit" class="w-full py-3.5 bg-primary hover:bg-primary-hover text-white font-bold rounded-xl transition shadow-md text-sm">
                        Gerar Nova Senha
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <a href="{{ route('login') }}" class="text-xs font-semibold text-primary hover:underline">
                        &larr; Voltar para o Login
                    </a>
                </div>
            </div>

        </div>
    </main>

    <footer class="py-6 border-t border-slate-200 bg-white flex-shrink-0">
        <div class="max-w-6xl mx-auto px-4 text-center text-xs text-slate-500">
            Biblió é uma iniciativa open source do grupo de pesquisa <a href="https://www.curio.ia.br" target="_blank" class="text-primary font-semibold">Curió (www.curio.ia.br)</a>.
        </div>
    </footer>

</body>
</html>
