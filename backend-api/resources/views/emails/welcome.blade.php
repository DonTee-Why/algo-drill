<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ config('app.name') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 30px; border-radius: 8px;">
        <h1 style="color: #2563eb; margin-top: 0;">Welcome to {{ config('app.name') }}!</h1>
        
        <p>Hello {{ $user->name }},</p>
        
        <p>Congratulations! Your email address has been successfully verified. You now have full access to all features of {{ config('app.name') }}.</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('dashboard') }}" style="background-color: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block; font-weight: bold;">Go to Dashboard</a>
        </div>
        
        <p>If you have any questions or need assistance, please don't hesitate to reach out to our support team.</p>
        
        <p style="margin-top: 20px;">
            Best regards,<br>
            The {{ config('app.name') }} Team
        </p>
    </div>
</body>
</html>

