@extends('emails.layout')

@section('title', 'Sua senha no Biblió foi alterada!')

@section('content')
<h2 style="color: #1E4D2B; font-size: 20px; font-weight: 700; margin-top: 0; margin-bottom: 16px;">Olá, {{ $user->name }}!</h2>

<p style="margin-top: 0; margin-bottom: 20px; color: #2C2C2C;">Informamos que a senha de acesso da sua conta no portal <strong>Biblió</strong> foi atualizada. Abaixo estão suas credenciais para login:</p>

<!-- Credenciais -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F7F5F0; border: 1px solid #E0DDD8; border-radius: 12px; margin-bottom: 24px; padding: 20px;">
    <tr>
        <td style="padding-bottom: 8px; font-size: 13px; color: #6B675E; font-weight: 600; text-transform: uppercase;">E-mail de acesso:</td>
    </tr>
    <tr>
        <td style="font-family: monospace; font-size: 15px; color: #1E4D2B; padding-bottom: 16px; font-weight: bold;">{{ $user->email }}</td>
    </tr>
    <tr>
        <td style="padding-bottom: 8px; font-size: 13px; color: #6B675E; font-weight: 600; text-transform: uppercase;">Nova Senha:</td>
    </tr>
    <tr>
        <td style="font-family: monospace; font-size: 15px; color: #2C2C2C; padding-bottom: 8px; font-weight: bold;">{{ $password }}</td>
    </tr>
</table>

<p style="color: #D32F2F; font-size: 12px; margin-bottom: 30px; font-weight: 500;">⚠️ Importante: Se você não solicitou esta alteração, por favor entre em contato com o suporte ou gestores da biblioteca imediatamente.</p>

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
