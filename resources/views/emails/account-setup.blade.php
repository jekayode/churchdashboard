@php($appName = $appName ?? config('app.name', 'Church Dashboard'))
<!doctype html>
<html>
  <body style="font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; line-height:1.6; color:#111827;">
    <h2 style="margin:0 0 12px 0;">Welcome to {{ $appName }}</h2>
    <p style="margin:0 0 12px 0;">Hi {{ $recipientName }},</p>
    <p style="margin:0 0 12px 0;">Use the button below to set your password and access your account.</p>
    <p style="margin:0 0 24px 0;"><a href="{{ $resetUrl }}" style="background:#2563eb;color:#fff;padding:10px 16px;border-radius:6px;text-decoration:none;">Set Your Password</a></p>
    <p style="margin:0 0 8px 0; font-size:14px; color:#374151;">If the button does not work, copy and paste this link into your browser:</p>
    <p style="word-break:break-all; font-size:12px; color:#374151;">{{ $resetUrl }}</p>
    <p style="margin-top:24px; font-size:14px; color:#374151;">Blessings,<br>{{ $appName }} Team</p>
  </body>
  </html>


