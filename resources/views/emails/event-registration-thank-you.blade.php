<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0;padding:0;background:#f4f4f5;font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;line-height:1.6;color:#111827;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f4f5;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:480px;background:#ffffff;border-radius:12px;border:1px solid #e5e7eb;padding:28px;">
                    <tr>
                        <td>
                            <h1 style="margin:0 0 12px 0;font-size:20px;font-weight:700;">Thank you for registering</h1>
                            <p style="margin:0 0 8px 0;font-size:15px;color:#374151;">Hi {{ $recipientName }},</p>
                            <p style="margin:0 0 16px 0;font-size:15px;color:#374151;">You are confirmed for <strong>{{ $eventName }}</strong>.</p>
                            <p style="margin:0 0 20px 0;font-size:14px;color:#6b7280;">We look forward to seeing you there.</p>
                            <a href="{{ $eventPageUrl }}" style="display:inline-block;background:#F1592A;color:#ffffff;text-decoration:none;font-weight:600;font-size:14px;padding:12px 20px;border-radius:8px;">View event</a>
                        </td>
                    </tr>
                </table>
                <p style="margin:16px 0 0;font-size:12px;color:#9ca3af;">{{ $appName }}</p>
            </td>
        </tr>
    </table>
</body>
</html>
