<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>New Invoice</title>
</head>

<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <p>Hi,</p>

    <p>Please find attached your invoice <strong>{{ $invoice->number }}</strong> from <strong>{{ $invoice->organization->name }}</strong>.</p>

    <p>
        <strong>Invoice Amount:</strong> {{ $invoice->currency }} {{ number_format($invoice->total_amount_cents / 100, 2) }}<br>
        <strong>Due Date:</strong> {{ $invoice->due_date->format('M d, Y') }}
    </p>

    <p style="margin: 30px 0;">
        <a href="{{ $url }}" style="background-color: #2563eb; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;">View & Pay Invoice</a>
    </p>

    <p>If the button above doesn't work, copy and paste this link into your browser:<br>
        <a href="{{ $url }}">{{ $url }}</a>
    </p>

    <p>If you have any questions, please contact us.</p>

    <p>Best regards,<br>
        {{ $invoice->organization->name }}
    </p>
</body>

</html>