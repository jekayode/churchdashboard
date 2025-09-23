<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password - LifePointe Church</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', Arial, sans-serif;
            background-color: #f8fafc;
            color: #374151;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #9DC83B;
            padding: 24px;
            text-align: center;
        }
        .logo {
            height: 48px;
            width: auto;
            margin-bottom: 16px;
        }
        .header h1 {
            color: #ffffff;
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }
        .content {
            padding: 32px 24px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 16px;
        }
        .message {
            font-size: 16px;
            line-height: 1.6;
            color: #6b7280;
            margin-bottom: 24px;
        }
        .button {
            display: inline-block;
            background-color: #F1592A;
            color: #ffffff;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            font-size: 16px;
            margin: 16px 0;
            transition: background-color 0.2s;
        }
        .button:hover {
            background-color: #e2421a;
        }
        .expiry {
            font-size: 14px;
            color: #6b7280;
            margin: 16px 0;
        }
        .disclaimer {
            font-size: 14px;
            color: #6b7280;
            margin: 16px 0;
        }
        .footer {
            background-color: #f8fafc;
            padding: 24px;
            border-top: 1px solid #e5e7eb;
        }
        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        .footer-left {
            flex: 1;
            min-width: 200px;
        }
        .footer-right {
            flex: 1;
            min-width: 200px;
            text-align: right;
        }
        .footer h3 {
            color: #374151;
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 8px 0;
        }
        .footer p {
            color: #6b7280;
            font-size: 14px;
            margin: 4px 0;
        }
        .social-icons {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            margin-top: 8px;
        }
        .social-icon {
            width: 32px;
            height: 32px;
            background-color: #9DC83B;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
        }
        .social-icon:hover {
            background-color: #8bb332;
        }
        .copyright {
            text-align: center;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 12px;
        }
        .fallback-url {
            background-color: #f3f4f6;
            padding: 12px;
            border-radius: 4px;
            margin-top: 16px;
            word-break: break-all;
            font-size: 12px;
            color: #6b7280;
        }
        .fallback-url a {
            color: #F1592A;
            text-decoration: none;
        }
        .fallback-url a:hover {
            text-decoration: underline;
        }
        @media (max-width: 600px) {
            .email-container {
                margin: 0;
                border-radius: 0;
            }
            .content {
                padding: 24px 16px;
            }
            .footer-content {
                flex-direction: column;
                text-align: center;
            }
            .footer-right {
                text-align: center;
            }
            .social-icons {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <img src="https://lifepointeng.org/wp-content/uploads/2023/10/Lifepointe-Logo-White.png" alt="LifePointe Church" class="logo">
            <h1>LifePointe Church</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">Hello {{ $user->name ?? 'there' }}!</div>
            
            <div class="message">
                You are receiving this email because we received a password reset request for your church dashboard account.
            </div>

            <div style="text-align: center;">
                <a href="{{ $actionUrl }}" class="button">Reset Password</a>
            </div>

            <div class="expiry">
                This password reset link will expire in {{ $expiration }} minutes.
            </div>

            <div class="disclaimer">
                If you did not request a password reset, no further action is required.
            </div>

            <!-- Fallback URL -->
            <div class="fallback-url">
                <strong>Having trouble clicking the button?</strong><br>
                Copy and paste the URL below into your web browser:<br>
                <a href="{{ $actionUrl }}">{{ $actionUrl }}</a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-content">
                <div class="footer-left">
                    <h3>Contact</h3>
                    <p>Pistis Annex, 3 Remi Olowude St,</p>
                    <p>Eti-Osa 105102, Lagos</p>
                </div>
                <div class="footer-right">
                    <h3>Follow Us</h3>
                    <div class="social-icons">
                        <a href="#" class="social-icon">f</a>
                        <a href="#" class="social-icon">t</a>
                        <a href="#" class="social-icon">i</a>
                        <a href="#" class="social-icon">in</a>
                    </div>
                </div>
            </div>
            <div class="copyright">
                LifePointe Church © All Rights Reserved
            </div>
        </div>
    </div>
</body>
</html>
