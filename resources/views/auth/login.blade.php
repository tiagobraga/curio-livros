<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Entrar — Biblió') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; font-family: 'Outfit', sans-serif; }

        /* ── Layout ── */
        .login-wrapper {
            display: flex;
            min-height: 100vh;
            background: #0d1117;
        }

        /* ── Left panel ── */
        .login-left {
            display: none;
            flex-direction: column;
            justify-content: space-between;
            width: 42%;
            flex-shrink: 0;
            background: linear-gradient(160deg, #1a4429 0%, #1E4D2B 40%, #163820 100%);
            padding: 48px 52px;
            position: relative;
            overflow: hidden;
        }

        @media (min-width: 900px) {
            .login-left { display: flex; }
        }

        /* Decorative circles */
        .login-left::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: rgba(255,255,255,0.03);
            bottom: -160px;
            right: -160px;
            pointer-events: none;
        }
        .login-left::after {
            content: '';
            position: absolute;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background: rgba(255,255,255,0.03);
            top: -60px;
            left: -80px;
            pointer-events: none;
        }

        .left-top { position: relative; z-index: 1; }

        .left-logo {
            width: 220px;
            height: auto;
            border-radius: 16px;
            display: block;
            margin: 0 auto 40px;
            background: rgba(255,255,255,0.08);
            padding: 12px;
        }

        .left-headline {
            font-size: 2.35rem;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.15;
            margin: 0 0 16px;
            letter-spacing: -0.5px;
        }

        .left-sub {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.6);
            line-height: 1.65;
            margin: 0;
        }

        /* Feature list */
        .left-features {
            position: relative;
            z-index: 1;
            list-style: none;
            margin: 44px 0 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .left-features li {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.875rem;
            color: rgba(255,255,255,0.8);
            font-weight: 500;
        }

        .feature-dot {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .feature-dot svg { width: 11px; height: 11px; color: #6ee7b7; }

        /* Register buttons on left panel */
        .left-register-btns {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 36px;
        }
        .left-register-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 18px;
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,0.18);
            background: rgba(255,255,255,0.07);
            color: rgba(255,255,255,0.85);
            font-family: 'Outfit', sans-serif;
            font-size: 0.88rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.2s, border-color 0.2s, color 0.2s;
            line-height: 1.2;
        }
        .left-register-btn:hover {
            background: rgba(255,255,255,0.13);
            border-color: rgba(255,255,255,0.3);
            color: #ffffff;
        }
        .left-register-btn svg { width: 16px; height: 16px; flex-shrink: 0; opacity: 0.7; }

        .left-bottom {
            position: relative;
            z-index: 1;
            font-size: 0.72rem;
            color: rgba(255,255,255,0.35);
            margin-top: 28px;
        }
        .left-bottom a { color: rgba(255,255,255,0.5); text-decoration: none; }
        .left-bottom a:hover { color: rgba(255,255,255,0.75); }

        /* ── Right panel (form) ── */
        .login-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 32px 24px;
            background: #F7F5F0;
            position: relative;
        }

        /* Top accent bar on mobile only */
        @media (max-width: 899px) {
            .login-right::before {
                content: '';
                position: absolute;
                top: 0; left: 0; right: 0;
                height: 3px;
                background: linear-gradient(90deg, #1E4D2B, #3d8f55, #1E4D2B);
            }
        }

        .login-form-wrap {
            width: 100%;
            max-width: 420px;
        }

        /* Mobile header & buttons (hidden on desktop) */
        .mobile-header-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 24px;
            text-align: center;
        }
        @media (min-width: 900px) {
            .mobile-header-wrap { display: none; }
        }
        .mobile-logo {
            width: 250px;
            max-width: 90%;
            height: auto;
            border-radius: 14px;
            background: rgba(30,77,43,0.06);
            padding: 10px;
            margin-bottom: 12px;
            display: block;
        }
        .mobile-logo-title {
            font-size: 1rem;
            font-weight: 700;
            color: #1E4D2B;
            margin-bottom: 14px;
            display: block;
        }
        .mobile-register-btns {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 20px;
        }
        .mobile-register-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 13px 18px;
            border-radius: 12px;
            border: 1px solid #1E4D2B;
            background: #ffffff;
            color: #1E4D2B;
            font-family: 'Outfit', sans-serif;
            font-size: 0.88rem;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.18s, color 0.18s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            line-height: 1.2;
        }
        .mobile-register-btn:hover, .mobile-register-btn:active {
            background: #1E4D2B;
            color: #ffffff;
        }
        .mobile-register-btn svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        /* Mobile footer (hidden on desktop) */
        .mobile-footer {
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            width: 100%;
            background: #1E4D2B;
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.75rem;
            padding: 16px 20px;
            margin-top: 32px;
            border-radius: 14px;
            line-height: 1.5;
            box-shadow: 0 4px 16px rgba(30,77,43,0.2);
        }
        @media (min-width: 900px) {
            .mobile-footer { display: none; }
        }
        .mobile-footer a {
            color: #ffffff;
            font-weight: 600;
            text-decoration: underline;
            margin: 0 3px;
        }
        .mobile-footer a:hover {
            color: #6ee7b7;
        }

        /* Section label */
        .form-section-label {
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: #1E4D2B;
            margin-bottom: 28px;
            display: block;
        }

        /* Alert banners */
        .alert {
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 0.82rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background: rgba(52,211,153,0.08); border: 1px solid rgba(52,211,153,0.2); color: #6ee7b7; }
        .alert-error   { background: rgba(248,113,113,0.08); border: 1px solid rgba(248,113,113,0.2); color: #fca5a5; }
        .alert svg { width: 16px; height: 16px; flex-shrink: 0; }

        /* Form fields */
        .field { margin-bottom: 16px; }

        .field label {
            display: block;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            color: #5a6b5e;
            margin-bottom: 7px;
            text-transform: uppercase;
        }

        .field input {
            width: 100%;
            background: #ffffff;
            border: 1px solid #ddd9d0;
            border-radius: 12px;
            padding: 13px 16px;
            font-family: 'Outfit', sans-serif;
            font-size: 0.92rem;
            color: #1a2e20;
            outline: none;
            transition: border-color 0.18s, box-shadow 0.18s;
            -webkit-appearance: none;
        }
        .field input::placeholder { color: #b0b8b2; }
        .field input:focus {
            border-color: #2d6b3e;
            box-shadow: 0 0 0 3px rgba(45,107,62,0.15);
        }
        .field input.has-error { border-color: rgba(220,38,38,0.5); }

        .field-error {
            font-size: 0.74rem;
            color: #f87171;
            margin-top: 5px;
            display: block;
        }

        /* Password wrapper */
        .pw-wrap { position: relative; }
        .pw-wrap input { padding-right: 44px; }
        .pw-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #9aaba0;
            padding: 0;
            display: flex;
            align-items: center;
            transition: color 0.15s;
        }
        .pw-toggle:hover { color: #5a6b5e; }
        .pw-toggle svg { width: 17px; height: 17px; }

        /* Remember me */
        .remember {
            display: flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 24px;
        }
        .remember input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #2d6b3e;
            cursor: pointer;
            flex-shrink: 0;
        }
        .remember label {
            font-size: 0.82rem;
            color: #5a6b5e;
            cursor: pointer;
            user-select: none;
        }

        /* Submit button */
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #1E4D2B, #2d6b3e);
            border: 1px solid #3d8f55;
            border-radius: 12px;
            color: #fff;
            font-family: 'Outfit', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, box-shadow 0.2s, transform 0.12s;
            box-shadow: 0 4px 20px rgba(30,77,43,0.4);
            letter-spacing: 0.2px;
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #2d6b3e, #3d8f55);
            box-shadow: 0 6px 28px rgba(61,143,85,0.4);
            transform: translateY(-1px);
        }
        .btn-submit:active { transform: translateY(0); }

        /* Links below form */
        .form-links {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }
        .form-link {
            font-size: 0.8rem;
            color: #7a8c80;
            text-decoration: none;
            transition: color 0.15s;
        }
        .form-link:hover { color: #1E4D2B; }
        .form-links-dot {
            width: 3px; height: 3px;
            border-radius: 50%;
            background: #c8c2b8;
        }

        /* Divider */
        .divider {
            border: none;
            border-top: 1px solid #e0dbd0;
            margin: 24px 0;
        }

        /* Register CTA */
        .register-cta {
            text-align: center;
            font-size: 0.82rem;
            color: #7a8c80;
        }
        .register-cta a {
            color: #1E4D2B;
            text-decoration: none;
            font-weight: 600;
        }
        .register-cta a:hover { color: #2d6b3e; }
    </style>
</head>
<body>
<div class="login-wrapper">

    {{-- ── LEFT PANEL ── --}}
    <div class="login-left">
        <div class="left-top">
            <img src="/images/bibliologo.jpg" alt="Biblió" class="left-logo">
            <h1 class="left-headline">{{ __('Bem-vindo à Biblió!') }}</h1>
            <p class="left-sub">{{ __('Biblió: Biblioteca Inteligente é um sistema opensource para gerenciamento de acervos com catálogo construído coletivamente por seus usuários.') }}</p>

            <ul class="left-features">
                <li>
                    <span class="feature-dot">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    </span>
                    {{ __('Catálogo colaborativo de acervos') }}
                </li>
                <li>
                    <span class="feature-dot">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    </span>
                    {{ __('Gestão de acervo e exemplares') }}
                </li>
                <li>
                    <span class="feature-dot">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    </span>
                    {{ __('Controle de empréstimos e devoluções') }}
                </li>
                <li>
                    <span class="feature-dot">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    </span>
                    {{ __('Múltiplas bibliotecas em rede') }}
                </li>
            </ul>
        </div>

        <!-- Registration links at bottom of left panel -->
        <div class="left-register-btns">
            <a href="{{ route('register') }}" class="left-register-btn" id="btn-vincule-biblioteca">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016 2.993 2.993 0 0 0 2.25-1.016 3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                </svg>
                {{ __('Se vincule a uma biblioteca.') }}
            </a>
            <a href="{{ route('register-library') }}" class="left-register-btn" id="btn-crie-biblioteca">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                {{ __('Crie sua própria biblioteca.') }}
            </a>
        </div>

        <div class="left-bottom">
            {{ __('Uma iniciativa open source do grupo de pesquisa') }}
            <a href="http://www.curio.ia.br" target="_blank">Curió</a>
            &nbsp;·&nbsp; CC-BY
        </div>
    </div>

    {{-- ── RIGHT PANEL ── --}}
    <div class="login-right">
        <!-- Language selector dropdown -->
        <div style="position: absolute; top: 20px; right: 24px; z-index: 10;">
            <select id="language-select-login" onchange="window.location.href='/language/' + this.value"
                style="background: #ffffff; color: #1E4D2B; font-size: 0.78rem; font-weight: 600; border: 1px solid #ddd9d0; border-radius: 10px; padding: 6px 12px; cursor: pointer; outline: none; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
                <option value="pt_BR" {{ app()->getLocale() === 'pt_BR' ? 'selected' : '' }}>Português (BR)</option>
                <option value="en_US" {{ app()->getLocale() === 'en_US' ? 'selected' : '' }}>English (US)</option>
                <option value="es" {{ app()->getLocale() === 'es' ? 'selected' : '' }}>Español</option>
            </select>
        </div>

        <div class="login-form-wrap">

            {{-- Mobile header (logo 180px, title & registration buttons) --}}
            <div class="mobile-header-wrap">
                <img src="/images/bibliologo.jpg" alt="Biblió" class="mobile-logo">
                <span class="mobile-logo-title">{{ __('Biblioteca Inteligente') }}</span>

                <div class="mobile-register-btns">
                    <a href="{{ route('register') }}" class="mobile-register-btn" id="mobile-btn-vincule-biblioteca">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016 2.993 2.993 0 0 0 2.25-1.016 3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                        </svg>
                        {{ __('Se vincule a uma biblioteca') }}
                    </a>
                    <a href="{{ route('register-library') }}" class="mobile-register-btn" id="mobile-btn-crie-biblioteca">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        {{ __('Crie sua própria biblioteca') }}
                    </a>
                </div>
            </div>

            <span class="form-section-label">{{ __('Acesso ao sistema') }}</span>

            {{-- Session alerts --}}
            @if(session('success'))
                <div class="alert alert-success">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('status'))
                <div class="alert alert-success">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    {{ session('status') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-error">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                    {{ __('E-mail ou senha incorretos. Verifique seus dados e tente novamente.') }}
                </div>
            @endif

            {{-- Login Form --}}
            <form action="{{ route('login.post') }}" method="POST" id="login-form">
                @csrf

                {{-- Email --}}
                <div class="field">
                    <label for="email">{{ __('E-mail') }}</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="{{ __('seu@email.com') }}"
                        required
                        autofocus
                        autocomplete="email"
                        class="{{ $errors->has('email') ? 'has-error' : '' }}"
                    >
                    @error('email')
                        @if($message !== 'These credentials do not match our records.')
                            <span class="field-error">{{ $message }}</span>
                        @endif
                    @enderror
                </div>

                {{-- Password --}}
                <div class="field">
                    <label for="password">{{ __('Senha') }}</label>
                    <div class="pw-wrap">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            required
                            autocomplete="current-password"
                            class="{{ $errors->has('password') ? 'has-error' : '' }}"
                        >
                        <button type="button" class="pw-toggle" id="btn-toggle-pw" title="{{ __('Mostrar/ocultar senha') }}" onclick="togglePw()">
                            <svg id="pw-eye" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <svg id="pw-eye-slash" style="display:none;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Remember me --}}
                <div class="remember">
                    <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember">{{ __('Manter sessão ativa neste dispositivo') }}</label>
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn-submit" id="btn-login">
                    {{ __('Entrar no sistema') }}
                </button>
            </form>

            @if(Route::has('password.request'))
                <div class="form-links">
                    <a href="{{ route('password.request') }}" class="form-link" id="link-forgot-password">{{ __('Esqueceu a senha?') }}</a>
                </div>
            @endif

            {{-- Mobile green footer --}}
            <div class="mobile-footer">
                <span>
                    {{ __('Uma iniciativa open source do grupo de pesquisa') }}
                    <a href="http://www.curio.ia.br" target="_blank" rel="noopener noreferrer">Curió</a>
                    &nbsp;·&nbsp; CC-BY
                </span>
            </div>

        </div>
    </div>

</div>

<script>
    function togglePw() {
        const input = document.getElementById('password');
        const eye = document.getElementById('pw-eye');
        const slash = document.getElementById('pw-eye-slash');
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        eye.style.display = isHidden ? 'none' : '';
        slash.style.display = isHidden ? '' : 'none';
    }
</script>
</body>
</html>
