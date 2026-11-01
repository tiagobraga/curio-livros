@extends('emails.layout')

@section('title', 'Recuperação de Senha — Biblió')

@section('content')
<h2 style="margin-top: 0; color: #1E4D2B; font-size: 18px; font-weight: 700;">Olá, {{ $user->name }}!</h2>

<p style="margin: 0 0 20px 0; color: #2C2C2C;">
    Você está recebendo este e-mail porque solicitou a redefinição de senha para a sua conta no sistema <strong>Biblió</strong>.
</p>

<div style="text-align: center; margin: 30px 0;">
    <a href="{{ $url }}" style="display: inline-block; background-color: #1E4D2B; color: #FFFFFF; font-weight: bold; font-size: 14px; text-decoration: none; padding: 12px 24px; border-radius: 12px; box-shadow: 0 4px 6px rgba(30, 77, 43, 0.15);">
        Redefinir Minha Senha
    </a>
</div>

<p style="margin: 0 0 20px 0; color: #2C2C2C;">
    Este link de redefinição de senha é seguro e expirará em <strong>60 minutos</strong>.
</p>

<p style="margin: 0 0 20px 0; color: #6B675E; font-size: 13px; border-left: 3px solid #E0DDD8; padding-left: 12px; font-style: italic;">
    Se você não solicitou a redefinição de senha, nenhuma ação adicional é necessária e você pode desconsiderar este e-mail.
</p>

<div style="margin-top: 30px; border-top: 1px solid #F7F5F0; pt-20px; font-size: 12px; color: #6B675E;">
    <p style="margin: 0 0 5px 0;">Se estiver com problemas para clicar no botão acima, copie e cole o endereço abaixo no seu navegador:</p>
    <a href="{{ $url }}" style="color: #1E4D2B; text-decoration: underline; word-break: break-all;">{{ $url }}</a>
</div>
@endsection
