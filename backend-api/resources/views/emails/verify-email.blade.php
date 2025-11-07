<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email Address</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 30px; border-radius: 8px;">
        <h1 style="color: #2563eb; margin-top: 0;">Verify Your Email Address</h1>
        
        <p>Hello {{ $user->name }},</p>
        
        <p>Thank you for registering with {{ config('app.name') }}! Please click the button below to verify your email address.</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $verificationUrl }}" style="background-color: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block; font-weight: bold;">Verify Email Address</a>
        </div>
        
        <p>If the button doesn't work, you can copy and paste the following link into your browser:</p>
        <p style="word-break: break-all; color: #2563eb;">{{ $verificationUrl }}</p>
        
        <p style="color: #6b7280; font-size: 14px; margin-top: 30px;">
            This verification link will expire in 60 minutes. If you did not create an account, please ignore this email.
        </p>
        
        <p style="margin-top: 20px;">
            Best regards,<br>
            The {{ config('app.name') }} Team
        </p>
    </div>
</body>
</html>

