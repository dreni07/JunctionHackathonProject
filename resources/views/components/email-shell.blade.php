@props([
    'accent' => '#10825B',
    'accentDark' => '#0E6E4D',
    'eyebrow' => 'Pyramid of Tirana',
    'subjectLine' => '',
    'body' => '',
    'footnote' => 'Sent by the Pyramid of Tirana operations team.',
])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine }}</title>
</head>
<body style="margin:0;padding:0;background:#F4F3EE;font-family:'Hanken Grotesk',-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F4F3EE;padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:18px;overflow:hidden;border:1px solid #E0DCD3;">
                    <tr>
                        <td style="background:linear-gradient(135deg,{{ $accent }},{{ $accentDark }});padding:26px 30px;">
                            <img src="{{ asset('assets/piramida-tiranes-logo.svg') }}" alt="Piramida e Tiranës" style="height:52px;width:auto;display:block;margin-bottom:14px;background:transparent;">
                            <div style="font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:rgba(255,255,255,0.82);">
                                {{ $eyebrow }}
                            </div>
                            <div style="margin-top:8px;font-size:22px;line-height:1.25;font-weight:800;color:#ffffff;">
                                {{ $subjectLine }}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 30px;color:#1A1A1A;font-size:15px;line-height:1.6;">
                            {!! nl2br(e($body)) !!}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 30px 28px;">
                            <div style="height:1px;background:#EAE7DC;margin-bottom:16px;"></div>
                            <div style="font-size:12px;color:#9A958B;">
                                {{ $footnote }}
                            </div>
                        </td>
                    </tr>
                </table>
                <div style="max-width:560px;margin:16px auto 0;font-size:11px;color:#B5B0A6;">
                    Pyramid of Tirana · Bulevardi Dëshmorët e Kombit, Tiranë
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
