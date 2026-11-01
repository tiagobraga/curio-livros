@extends('emails.layout')

@section('title', 'Sua conta no Biblió foi criada!')

@section('content')
<h2 style="color: #1E4D2B; font-size: 20px; font-weight: 700; margin-top: 0; margin-bottom: 16px;">Olá, {{ $user->name }}!</h2>

<p style="margin-top: 0; margin-bottom: 20px; color: #2C2C2C;">Sua conta de acesso ao portal <strong>Biblió</strong> foi cadastrada com sucesso. Abaixo estão suas credenciais de login e as bibliotecas vinculadas à sua conta.</p>

<!-- Credenciais -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F7F5F0; border: 1px solid #E0DDD8; border-radius: 12px; margin-bottom: 24px; padding: 20px;">
    <tr>
        <td style="padding-bottom: 8px; font-size: 13px; color: #6B675E; font-weight: 600; text-transform: uppercase;">E-mail de acesso:</td>
    </tr>
    <tr>
        <td style="font-family: monospace; font-size: 15px; color: #1E4D2B; padding-bottom: 16px; font-weight: bold;">{{ $user->email }}</td>
    </tr>
    <tr>
        <td style="padding-bottom: 8px; font-size: 13px; color: #6B675E; font-weight: 600; text-transform: uppercase;">Senha temporária:</td>
    </tr>
    <tr>
        <td style="font-family: monospace; font-size: 15px; color: #2C2C2C; padding-bottom: 16px; font-weight: bold;">{{ $password }}</td>
    </tr>
    <tr>
        <td style="padding-bottom: 8px; font-size: 13px; color: #6B675E; font-weight: 600; text-transform: uppercase;">Perfil de Acesso:</td>
    </tr>
    <tr>
        <td style="font-size: 14px; color: #2C2C2C;">
            @if($user->role === 'admin') Administrador
            @elseif($user->role === 'curator') Curador
            @elseif($user->role === 'manager') Gestor
            @elseif($user->role === 'attendant') Atendente
            @else Leitor @endif
        </td>
    </tr>
</table>

<!-- Bibliotecas -->
<h3 style="color: #1E4D2B; font-size: 15px; font-weight: 600; margin-top: 0; margin-bottom: 12px;">Unidades e Bibliotecas Associadas:</h3>
<ul style="margin: 0; padding-left: 20px; margin-bottom: 30px;">
    @foreach($user->libraries as $lib)
        <li style="margin-bottom: 6px; color: #2C2C2C;">{{ $lib->name }}</li>
    @endforeach
</ul>

<!-- Botão de Acesso -->
<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td align="center">
            <a href="{{ route('login') }}" style="display: inline-block; background-color: #1E4D2B; color: #ffffff; text-decoration: none; padding: 12px 30px; font-size: 14px; font-weight: 600; border-radius: 12px; box-shadow: 0 4px 6px rgba(30, 77, 43, 0.15);">
                Acessar Minha Conta
            </a>
        </td>
    </tr>
</table>
@endsection
