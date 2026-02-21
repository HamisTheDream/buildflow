<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>BuildFlow features you shouldn't miss ✨</title>
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

        .feature {
            margin-bottom: 25px;
            padding-left: 15px;
            border-left: 4px solid #f97316;
        }

        .feature h3 {
            color: #111827;
            font-size: 16px;
            margin: 0 0 5px 0;
        }

        .feature p {
            margin: 0;
            font-size: 14px;
            color: #6b7280;
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
            <h1>Unlock BuildFlow's Full Potential ✨</h1>
        </div>
        <div class="content">
            <h2>Hi {{ $user->name }},</h2>
            <p>You've been using BuildFlow for a few days now. Did you know you can do more than just basic project tracking? Here are three powerful features our users love:</p>

            <div class="feature">
                <h3>1. Streamlined Financials 💰</h3>
                <p>Track project budgets, record expenses instantly, and send professional invoices directly to your clients from within the app.</p>
            </div>

            <div class="feature">
                <h3>2. Daily Site Logs 📋</h3>
                <p>Keep everyone in the loop. Site managers can log daily weather, workers present, tasks completed, and upload photo attachments straight from their phones.</p>
            </div>

            <div class="feature">
                <h3>3. Team Collaboration 🤝</h3>
                <p>Invite your entire team and assign tasks. Everyone stays perfectly synchronized, and nothing falls through the cracks.</p>
            </div>

            <div style="text-align: center; margin-top: 35px;">
                <a href="{{ config('app.url') }}/login" class="btn">Explore These Features</a>
            </div>

            <p style="margin-top: 30px;">Reply to this email if you'd like a quick 15-minute walkthrough with one of our product experts!</p>
            <p>Best regards,<br>The BuildFlow Team</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} BuildFlow. All rights reserved.
        </div>
    </div>
</body>

</html>