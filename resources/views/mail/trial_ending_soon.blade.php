<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Your BuildFlow Trial Ends Soon ⏰</title>
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
            background-color: #ef4444;
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
            margin-bottom: 20px;
        }

        .warning-box {
            background-color: #fef2f2;
            border: 1px solid #fca5a5;
            padding: 15px;
            border-radius: 6px;
            color: #991b1b;
            font-weight: 500;
            text-align: center;
            margin-bottom: 25px;
        }

        .btn {
            display: inline-block;
            background-color: #f97316;
            color: #ffffff;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 20px;
            font-size: 16px;
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
            <h1>Action Required: Trial Expiring</h1>
        </div>
        <div class="content">
            <h2>Hi there,</h2>

            <div class="warning-box">
                Your BuildFlow trial for <strong>{{ $organization->name }}</strong> expires in 3 days.
            </div>

            <p>We hope you've enjoyed your 14-day free trial and experienced how BuildFlow can streamline your construction projects.</p>
            <p>To avoid any interruption in tracking your projects, managing your team, or accessing your financial data, please upgrade your account today.</p>

            <div style="text-align: center; margin-top: 35px; margin-bottom: 25px;">
                <a href="{{ config('app.url') }}/owner/billing" class="btn">Upgrade Now</a>
            </div>

            <p>If you don't upgrade, your account will be paused, and you won't be able to log new site data or create new projects until a plan is selected.</p>
            <p>Have questions about our pricing? Reply to this email, and we'd be happy to help!</p>
            <p>Best regards,<br>The BuildFlow Team</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} BuildFlow. All rights reserved.
        </div>
    </div>
</body>

</html>