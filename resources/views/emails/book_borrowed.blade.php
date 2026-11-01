@extends('emails.layout')

@section('title', 'Empréstimo registrado com sucesso!')

@section('content')
<h2 style="color: #1E4D2B; font-size: 20px; font-weight: 700; margin-top: 0; margin-bottom: 16px;">Olá, {{ $loan->user->name }}!</h2>

<p style="margin-top: 0; margin-bottom: 20px; color: #2C2C2C;">Confirmamos que o empréstimo do livro abaixo foi registrado em nosso sistema. Por favor, atente-se à data limite de devolução para evitar suspensão ou penalidades.</p>

<!-- Detalhes do Empréstimo -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F7F5F0; border: 1px solid #E0DDD8; border-radius: 12px; margin-bottom: 24px; padding: 20px;">
    <tr>
        <td style="padding-bottom: 8px; font-size: 13px; color: #6B675E; font-weight: 600; text-transform: uppercase;">Livro:</td>
    </tr>
    <tr>
        <td style="font-size: 15px; color: #2C2C2C; padding-bottom: 16px; font-weight: 700;">{{ $loan->localCopy->centralItem->title }}</td>
    </tr>
    <tr>
        <td style="padding-bottom: 8px; font-size: 13px; color: #6B675E; font-weight: 600; text-transform: uppercase;">Unidade da Biblioteca:</td>
    </tr>
    <tr>
        <td style="font-size: 14px; color: #2C2C2C; padding-bottom: 16px;">{{ $loan->localCopy->library->name }}</td>
    </tr>
    <tr>
        <td style="padding-bottom: 8px; font-size: 13px; color: #6B675E; font-weight: 600; text-transform: uppercase;">Data de Empréstimo:</td>
    </tr>
    <tr>
        <td style="font-size: 14px; color: #2C2C2C; padding-bottom: 16px;">{{ $loan->loan_date->format('d/m/Y') }}</td>
    </tr>
    <tr>
        <td style="padding-bottom: 8px; font-size: 13px; color: #6B675E; font-weight: 600; text-transform: uppercase;">Data Limite para Devolução:</td>
    </tr>
    <tr>
        <td style="font-size: 15px; color: #D32F2F; font-weight: 700; padding-bottom: 16px;">{{ $loan->due_date->format('d/m/Y') }}</td>
    </tr>
    <tr>
        <td style="padding-bottom: 8px; font-size: 13px; color: #6B675E; font-weight: 600; text-transform: uppercase;">Atendente Responsável:</td>
    </tr>
    <tr>
        <td style="font-size: 14px; color: #2C2C2C;">{{ $loan->attendant->name }}</td>
    </tr>
</table>

<p style="margin-top: 0; margin-bottom: 20px; font-size: 13px; color: #6B675E;">Desejamos a você uma excelente leitura! Caso precise prorrogar o empréstimo, procure o atendente da unidade antes do vencimento.</p>
@endsection
