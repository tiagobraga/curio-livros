@extends('emails.layout')

@section('title', 'Devolução confirmada com sucesso!')

@section('content')
<h2 style="color: #1E4D2B; font-size: 20px; font-weight: 700; margin-top: 0; margin-bottom: 16px;">Olá, {{ $loan->user->name }}!</h2>

<p style="margin-top: 0; margin-bottom: 20px; color: #2C2C2C;">Confirmamos que a devolução do livro abaixo foi registrada com sucesso em nosso sistema de biblioteca. Sua pendência para este exemplar foi encerrada.</p>

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
        <td style="padding-bottom: 8px; font-size: 13px; color: #6B675E; font-weight: 600; text-transform: uppercase;">Data de Devolução:</td>
    </tr>
    <tr>
        <td style="font-size: 15px; color: #1E4D2B; font-weight: 700; padding-bottom: 16px;">{{ $loan->return_date->format('d/m/Y') }}</td>
    </tr>
</table>

<p style="margin-top: 0; margin-bottom: 20px; font-size: 13px; color: #6B675E;">Obrigado por colaborar com a circulação e conservação do nosso acervo! Sinta-se à vontade para escolher sua próxima leitura no portal Biblió.</p>
@endsection
