@php($accent = '#F1592A')
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
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;">
                    <tr>
                        <td style="padding:28px 28px 8px 28px;">
                            @if(!empty($branchName))
                                <p style="margin:0 0 8px 0;font-size:13px;color:#6b7280;">{{ $branchName }}</p>
                            @endif
                            <p style="margin:0 0 6px 0;font-size:15px;color:#374151;">You have registered for</p>
                            <h1 style="margin:0;font-size:26px;font-weight:700;line-height:1.25;color:#111827;">{{ $eventName }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 28px;">
                            <hr style="border:none;border-top:1px solid #e5e7eb;margin:16px 0;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 28px 8px 28px;">
                            @if($scheduleLine !== '')
                                <p style="margin:0 0 12px 0;font-size:14px;">
                                    <span style="display:inline-block;width:22px;color:{{ $accent }};">&#128197;</span>
                                    <strong style="color:#111827;">When</strong><br>
                                    <span style="margin-left:28px;display:inline-block;color:#374151;">{{ $scheduleLine }}</span>
                                </p>
                            @endif
                            @if($locationLine !== '')
                                <p style="margin:0;font-size:14px;">
                                    <span style="display:inline-block;width:22px;color:{{ $accent }};">&#128205;</span>
                                    <strong style="color:#111827;">Where</strong><br>
                                    <span style="margin-left:28px;display:inline-block;color:#374151;">{{ $locationLine }}</span>
                                </p>
                            @endif
                        </td>
                    </tr>
                    @if(!empty($eventDescription))
                        <tr>
                            <td style="padding:8px 28px 0 28px;">
                                <p style="margin:0;font-size:14px;color:#4b5563;">{{ \Illuminate\Support\Str::limit(strip_tags($eventDescription), 400) }}</p>
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td style="padding:24px 28px 8px 28px;">
                            <p style="margin:0 0 12px 0;font-size:13px;color:#6b7280;">A calendar invite is attached — add it to your calendar so you do not miss it.</p>
                            <table role="presentation" cellspacing="0" cellpadding="0" style="width:100%;">
                                <tr>
                                    <td style="padding:4px 8px 4px 0;vertical-align:middle;">
                                        <a href="{{ $eventPageUrl }}" style="display:inline-block;background:{{ $accent }};color:#ffffff;text-decoration:none;font-weight:600;font-size:14px;padding:12px 20px;border-radius:9999px;">Event page</a>
                                    </td>
                                    <td style="padding:4px 0 4px 8px;vertical-align:middle;">
                                        <a href="{{ $checkInUrl }}" style="display:inline-block;background:#e5e7eb;color:#111827;text-decoration:none;font-weight:600;font-size:14px;padding:12px 20px;border-radius:9999px;">My ticket</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 28px 28px 28px;">
                            <p style="margin:0;font-size:13px;color:#6b7280;">We also sent a separate email to help you set your account password if this is your first time on {{ $appName }}.</p>
                        </td>
                    </tr>
                </table>
                <p style="margin:16px 0 0;font-size:12px;color:#9ca3af;">{{ $appName }}</p>
            </td>
        </tr>
    </table>
</body>
</html>
