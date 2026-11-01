<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
</head>
<body style="margin: 0; padding: 0; background-color: #F7F5F0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #2C2C2C; -webkit-font-smoothing: antialiased;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F7F5F0; padding: 40px 20px;">
        <tr>
            <td align="center">
                <!-- Outer Container -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #FFFFFF; border: 1px solid #E0DDD8; border-radius: 24px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                    
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 32px 40px 20px 40px; background-color: #FFFFFF; border-bottom: 1px solid #F7F5F0;">
                            <img src="https://biblioteca.curio.ia.br/images/bibliologo.jpg"
                                 alt="Biblió"
                                 width="120"
                                 style="display: block; margin: 0 auto; border: 0; max-width: 120px; height: auto;">
                            <div style="font-size: 11px; color: #6B675E; margin-top: 8px; text-transform: uppercase; letter-spacing: 2px; font-weight: 600;">Biblioteca Inteligente</div>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 30px 40px 40px 40px; line-height: 1.6; font-size: 14px; color: #2C2C2C;">
                            @yield('content')
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 30px 40px; background-color: #F7F5F0; border-top: 1px solid #E0DDD8; font-size: 11px; color: #6B675E;">
                            <p style="margin: 0 0 8px 0;">Este é um e-mail automático enviado pelo sistema Biblió.</p>
                            <p style="margin: 0;">&copy; {{ date('Y') }} Biblió. Todos os direitos reservados.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
