<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Invoice {{ $invoice->number }}</title>
    <style>
        @page {
            margin: 40px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #1f2937;
            font-size: 14px;
            line-height: 1.5;
        }

        .header {
            margin-bottom: 40px;
            width: 100%;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 20px;
        }

        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #2563eb;
            text-transform: uppercase;
            letter-spacing: -0.5px;
        }

        .invoice-title {
            font-size: 32px;
            font-weight: 800;
            color: #111827;
            text-align: right;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .invoice-meta {
            text-align: right;
            margin-top: 10px;
            color: #6b7280;
            font-size: 13px;
        }

        .invoice-meta span {
            display: inline-block;
            min-width: 100px;
            color: #374151;
            font-weight: 600;
        }

        .grid {
            width: 100%;
            margin-bottom: 40px;
        }

        .grid td {
            vertical-align: top;
        }

        .bill-to {
            width: 50%;
        }

        .bill-to h3 {
            font-size: 12px;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 8px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .bill-to p {
            margin: 0;
            font-size: 15px;
            color: #111827;
        }

        .bill-to .email {
            font-size: 13px;
            color: #4b5563;
            margin-top: 4px;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        table.items th {
            background-color: #f9fafb;
            color: #374151;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            letter-spacing: 0.5px;
        }

        table.items td {
            padding: 16px;
            border-bottom: 1px solid #e5e7eb;
            color: #1f2937;
        }

        table.items td.qty,
        table.items td.price,
        table.items td.total {
            text-align: right;
        }

        table.items tr:last-child td {
            border-bottom: none;
        }

        .totals {
            width: 40%;
            margin-left: auto;
        }

        .totals table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals td {
            padding: 8px 0;
            text-align: right;
        }

        .totals .label {
            color: #6b7280;
            font-weight: 500;
        }

        .totals .value {
            color: #111827;
            font-weight: 600;
            width: 120px;
        }

        .totals .grand-total {
            font-size: 18px;
            border-top: 2px solid #2563eb;
            padding-top: 15px;
            margin-top: 10px;
            color: #2563eb;
            font-weight: 800;
        }

        .footer {
            margin-top: 60px;
            border-top: 1px solid #e5e7eb;
            padding-top: 30px;
            text-align: center;
            color: #9ca3af;
            font-size: 12px;
        }

        .notes {
            background-color: #f3f4f6;
            padding: 20px;
            border-radius: 6px;
            margin-top: 30px;
            font-size: 13px;
            color: #4b5563;
        }

        .notes h4 {
            margin: 0 0 5px;
            font-size: 12px;
            text-transform: uppercase;
            color: #374151;
        }
    </style>
</head>

<body>
    <div class="header">
        <table width="100%">
            <tr>
                <td width="50%" class="logo">
                    {{ $invoice->organization->name }}
                </td>
                <td width="50%">
                    <div class="invoice-title">Invoice</div>
                    <div class="invoice-meta">
                        <div><span>Reference:</span> #{{ $invoice->number }}</div>
                        <div><span>Date:</span> {{ $invoice->issue_date->format('M d, Y') }}</div>
                        @if($invoice->due_date)
                        <div><span>Due Date:</span> {{ $invoice->due_date->format('M d, Y') }}</div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="grid">
        <tr>
            <td class="bill-to">
                <h3>Bill To</h3>
                @if($invoice->clientUser)
                <p><strong>{{ $invoice->clientUser->name }}</strong></p>
                <p class="email">{{ $invoice->clientUser->email }}</p>
                @if($invoice->clientUser->address)<p style="margin-top:5px; font-size:13px; color:#6b7280;">{!! nl2br(e($invoice->clientUser->address)) !!}</p>@endif
                @elseif($invoice->clientLead)
                <p><strong>{{ $invoice->clientLead->first_name }} {{ $invoice->clientLead->last_name }}</strong></p>
                <p class="email">{{ $invoice->clientLead->email }}</p>
                @else
                <p>Guest Client</p>
                @endif
            </td>
            <td class="bill-to" style="padding-left: 40px;">
                @if($invoice->project)
                <h3>Project</h3>
                <p><strong>{{ $invoice->project->name }}</strong></p>
                @endif
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Description</th>
                <th width="80" style="text-align:right">Qty</th>
                <th width="100" style="text-align:right">Price</th>
                <th width="120" style="text-align:right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->meta['items'] ?? [] as $item)
            <tr>
                <td>
                    <div style="font-weight:600;">{{ $item['description'] ?? 'Item' }}</div>
                </td>
                <td class="qty">{{ $item['quantity'] ?? 1 }}</td>
                <td class="price">{{ number_format($item['unit_price'] ?? 0, 2) }}</td>
                <td class="total">{{ number_format(($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0), 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td class="label">Subtotal</td>
                <td class="value">{{ number_format($invoice->total_amount_cents / 100, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Tax (0%)</td>
                <td class="value">0.00</td>
            </tr>
            <tr>
                <td class="label grand-total">Total Due</td>
                <td class="value grand-total">{{ $invoice->currency ?? 'NGN' }} {{ number_format($invoice->total_amount_cents / 100, 2) }}</td>
            </tr>
        </table>
    </div>

    @if($invoice->notes)
    <div class="notes">
        <h4>Notes</h4>
        {{ $invoice->notes }}
    </div>
    @endif

    <div class="footer">
        <p>Payment due by {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'receipt' }}. Thank you for your business.</p>
        <p>{{ $invoice->organization->name }} &bull; Generated via BuildFlow</p>
    </div>
</body>

</html>