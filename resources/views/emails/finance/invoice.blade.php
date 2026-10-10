<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Invoice from {{ $invoice->organization->name }}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2 style="color: #1a1a1a;">New Invoice from {{ $invoice->organization->name }}</h2>

    <p>Hi,</p>

    <p>Please find your invoice <strong>{{ $invoice->number }}</strong> from <strong>{{ $invoice->organization->name }}</strong>.</p>

    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <tr>
            <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold;">Invoice Amount:</td>
            <td style="padding: 10px; border: 1px solid #ddd;">{{ $invoice->currency }} {{ number_format($invoice->total_amount_cents / 100, 2) }}</td>
        </tr>
        <tr>
            <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold;">Due Date:</td>
            <td style="padding: 10px; border: 1px solid #ddd;">{{ $invoice->due_date->format('M d, Y') }}</td>
        </tr>
    </table>

    <p style="margin: 30px 0;">
        <a href="{{ $url }}" style="background-color: #4f46e5; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">View &amp; Pay Invoice</a>
    </p>

    <p>If you have any questions, please contact us.</p>

    <p>Best regards,<br>{{ $invoice->organization->name }}</p>
</body>
</html>
