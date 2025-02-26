<!DOCTYPE html>
<html>
<head>
    <title>Reset Your Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(45deg, #dd6013, #ffbd00);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            border: 1px solid #ddd;
            border-top: none;
            padding: 20px;
            border-radius: 0 0 5px 5px;
        }
        .button {
            display: inline-block;
            background: linear-gradient(45deg, #dd6013, #ffbd00);
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reset Your Password</h1>
    </div>
    <div class="content">
        <p>Hello,</p>
        <p>You are receiving this email because we received a password reset request for your account.</p>
        <p>Please click the button below to reset your password:</p>
        
        <a href="{{ $mailData['resetUrl'] }}" class="button">Reset Password</a>
        
        <p>If you did not request a password reset, no further action is required.</p>
        <p>This password reset link will expire in 60 minutes.</p>
        
        <p>Regards,<br>The Restaurant Team</p>
    </div>
    <div class="footer">
        <p>If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser: {{ $mailData['resetUrl'] }}</p>
    </div>
</body>
</html>
