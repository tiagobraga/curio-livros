<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar — Curió Livros</title>
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
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-blue-900/40 via-slate-950 to-slate-950 relative overflow-hidden">
    <!-- Ambient decorative blobs -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-600/10 rounded-full filter blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-amber-500/5 rounded-full filter blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md z-10">
        <!-- Card Container -->
        <div class="bg-slate-900/60 backdrop-blur-xl border border-slate-800 rounded-3xl p-8 shadow-2xl shadow-blue-950/20">
            <!-- Header/Logo -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl overflow-hidden mb-4 border border-slate-700/50 shadow-inner bg-slate-950">
                    <img src="/images/curiologo.jpeg" alt="Logo Curió" class="w-full h-full object-cover">
                </div>
                <h1 class="text-2xl font-bold tracking-tight bg-gradient-to-r from-blue-400 via-slate-100 to-amber-300 bg-clip-text text-transparent">Curió Livros</h1>
                <p class="text-sm text-slate-400 mt-1">Gestão inteligente de acervos bibliográficos</p>
            </div>

            <!-- Login Form -->
            <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Email field -->
                <div class="space-y-1.5">
                    <label for="email" class="text-xs font-medium uppercase tracking-wider text-slate-400">E-mail</label>
                    <div class="relative">
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                            class="w-full bg-slate-950/80 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-200"
                            placeholder="seuemail@curio.ia.br">
                    </div>
                    @error('email')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password field -->
                <div class="space-y-1.5">
                    <div class="flex justify-between items-center">
                        <label for="password" class="text-xs font-medium uppercase tracking-wider text-slate-400">Senha</label>
                    </div>
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                            class="w-full bg-slate-950/80 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-200"
                            placeholder="••••••••">
                    </div>
                    @error('password')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember me -->
                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember" class="w-4 h-4 rounded border-slate-800 bg-slate-950 text-blue-600 focus:ring-blue-500 focus:ring-offset-slate-900">
                    <label for="remember" class="text-sm text-slate-400 ml-2 select-none">Lembrar-me neste dispositivo</label>
                </div>

                <!-- Submit button -->
                <button type="submit"
                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-medium rounded-xl py-3 text-sm transition duration-200 shadow-lg shadow-blue-500/25 active:scale-[0.98]">
                    Entrar no Sistema
                </button>
            </form>
        </div>

        <!-- Footer link -->
        <p class="text-center text-xs text-slate-600 mt-6">
            &copy; 2026 Grupo de Pesquisas Curió. Todos os direitos reservados.
        </p>
    </div>
</body>
</html>
