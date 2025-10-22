<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your OTP Code</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(90deg, #007bff, #00b4d8);
            color: white;
            text-align: center;
            padding: 24px 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            letter-spacing: 1px;
        }
        .content {
            padding: 30px 40px;
            text-align: center;
        }
        .content p {
            color: #555;
            font-size: 16px;
            line-height: 1.6;
        }
        .otp-box {
            display: inline-block;
            background: #f0f7ff;
            color: #007bff;
            font-weight: bold;
            font-size: 32px;
            padding: 14px 36px;
            border-radius: 8px;
            margin: 20px 0;
            letter-spacing: 4px;
            border: 2px dashed #007bff;
        }
        .footer {
            background: #f9fafb;
            text-align: center;
            padding: 18px;
            font-size: 14px;
            color: #999;
        }
        .footer a {
            color: #007bff;
            text-decoration: none;
        }
        @media (max-width: 600px) {
            .content {
                padding: 20px;
            }
            .otp-box {
                font-size: 26px;
                padding: 10px 24px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="header">
            <h1>🔐 Email Verification</h1>
        </div>

        <div class="content">
            <p>Hello,</p>
            <p>Thank you for registering! Please use the following One-Time Password (OTP) to verify your email address.</p>

            <div class="otp-box">{{ $otp ?? '------' }}</div>

            <p>This OTP will expire in <strong>10 minutes</strong>. Please do not share it with anyone.</p>
            <p>If you did not request this, please ignore this email.</p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Easy Library. All rights reserved.</p>
            <p><a href="https://yourdomain.com">Visit our website</a></p>
        </div>
    </div>
</body>
</html>
