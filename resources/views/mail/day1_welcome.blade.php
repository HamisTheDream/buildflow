<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Welcome to BuildFlow! 🚀</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f9fafb;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .header {
            background-color: #f97316;
            padding: 30px;
            text-align: center;
            color: white;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }

        .content {
            padding: 40px 30px;
            color: #374151;
            line-height: 1.6;
        }

        .content h2 {
            color: #111827;
            font-size: 20px;
            font-weight: 600;
            margin-top: 0;
        }

        .btn {
            display: inline-block;
            background-color: #f97316;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 20px;
        }

        .footer {
            background-color: #f3f4f6;
            padding: 20px 30px;
            text-align: center;
            font-size: 13px;
            color: #6b7280;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to BuildFlow! 🚀</h1>
        </div>
        <div class="content">
            <h2>Hi {{ $user->name }},</h2>
            <p>Welcome aboard! We are thrilled to have you join BuildFlow. Our platform is designed to make construction project management seamless, efficient, and stress-free.</p>
            <p>To get the most out of BuildFlow, we recommend starting by setting up your first project and inviting your team members to collaborate.</p>
            <div style="text-align: center;">
                <a href="{{ config('app.url') }}/login" class="btn">Go to Dashboard</a>
            </div>
            <p style="margin-top: 30px;">If you have any questions or need help setting up, just reply to this email, and our team will be right with you.</p>
            <p>Cheers,<br>The BuildFlow Team</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} BuildFlow. All rights reserved.
        </div>
    </div>
</body>

</html>