<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Recuperación de contraseña</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f5f7;font-family:Helvetica,Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f5f7;padding:32px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="480" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:8px;overflow:hidden;">
                    <tr>
                        <td style="background-color:#1f2937;padding:24px 32px;">
                            <span style="color:#ffffff;font-size:20px;font-weight:bold;">ExamenTAP</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="font-size:16px;color:#111827;margin:0 0 16px;">Hola {{ $name }},</p>
                            <p style="font-size:15px;color:#374151;line-height:1.6;margin:0 0 16px;">
                                Recibimos una solicitud para recuperar el acceso a tu cuenta. Hemos generado una nueva contraseña temporal para que puedas iniciar sesión:
                            </p>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
                                <tr>
                                    <td style="background-color:#f3f4f6;border-radius:6px;padding:16px;text-align:center;">
                                        <span style="font-size:20px;font-weight:bold;letter-spacing:1px;color:#111827;">{{ $temporaryPassword }}</span>
                                    </td>
                                </tr>
                            </table>
                            <p style="font-size:14px;color:#6b7280;line-height:1.6;margin:0 0 8px;">
                                Por tu seguridad, te recomendamos iniciar sesión con esta contraseña temporal y cambiarla de inmediato.
                            </p>
                            <p style="font-size:14px;color:#6b7280;line-height:1.6;margin:0;">
                                Si no solicitaste este cambio, contacta al equipo de soporte lo antes posible.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 32px;background-color:#f9fafb;">
                            <p style="font-size:12px;color:#9ca3af;margin:0;">Este es un mensaje automático, por favor no respondas a este correo.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
