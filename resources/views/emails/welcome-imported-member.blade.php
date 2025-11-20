<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ $branchName }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .welcome-title {
            color: #27ae60;
            font-size: 28px;
            margin: 20px 0;
        }
        .content {
            margin-bottom: 30px;
        }
        .credentials-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .credential-item {
            margin: 10px 0;
            font-size: 16px;
        }
        .credential-label {
            font-weight: bold;
            color: #495057;
        }
        .credential-value {
            color: #007bff;
            font-family: 'Courier New', monospace;
            background-color: #e9ecef;
            padding: 2px 6px;
            border-radius: 4px;
        }
        .security-notice {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        .security-notice h3 {
            color: #856404;
            margin-top: 0;
        }
        .cta-button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
        }
        .cta-button:hover {
            background-color: #0056b3;
        }
        .features-list {
            background-color: #e8f5e8;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .features-list ul {
            margin: 0;
            padding-left: 20px;
        }
        .features-list li {
            margin: 8px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 14px;
        }
        .highlight {
            background-color: #fff3cd;
            padding: 2px 4px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">{{ $appName }}</div>
            <h1 class="welcome-title">Welcome to {{ $branchName }}!</h1>
        </div>

        <div class="content">
            <p>Dear {{ $recipientName }},</p>
            
            <p>We're excited to welcome you to our church family! Your information has been added to our church management system, and we've created an account for you to access our online platform.</p>

            <div class="credentials-box">
                <h3 style="margin-top: 0; color: #495057;">Your Login Credentials:</h3>
                <div class="credential-item">
                    <span class="credential-label">Email:</span>
                    <span class="credential-value">{{ $email }}</span>
                </div>
                <div class="credential-item">
                    <span class="credential-label">Temporary Password:</span>
                    <span class="credential-value">{{ $temporaryPassword }}</span>
                </div>
            </div>

            <div class="security-notice">
                <h3>🔒 Important Security Notice</h3>
                <ul>
                    <li><strong>Please change your password immediately</strong> after your first login</li>
                    <li>Keep your login credentials secure and do not share them with others</li>
                    <li>If you suspect unauthorized access, contact us immediately</li>
                </ul>
            </div>

            <div style="text-align: center;">
                <a href="{{ $resetUrl }}" class="cta-button">Set Up Your Password</a>
            </div>

            <div class="features-list">
                <h3 style="margin-top: 0; color: #155724;">What you can do with your account:</h3>
                <ul>
                    <li>View and manage your church profile</li>
                    <li>Register for upcoming events and services</li>
                    <li>Connect with small groups and ministries</li>
                    <li>Update your contact information and preferences</li>
                    <li>Access church resources and announcements</li>
                    <li>View your giving history and manage contributions</li>
                </ul>
            </div>

            <p>If you have any questions or need assistance setting up your account, please don't hesitate to contact our church office. We're here to help!</p>

            <p>We look forward to seeing you at our next service and getting to know you better as part of our church family.</p>
        </div>

        <div class="footer">
            <p>Blessings,<br>
            <strong>The {{ $branchName }} Team</strong></p>
            <p style="font-size: 12px; margin-top: 20px;">
                This email was sent because your information was added to our church management system. 
                If you believe this was sent in error, please contact us.
            </p>
        </div>
    </div>
</body>
</html>











