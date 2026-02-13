<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $project->name }} - Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
        }

        .header {
            background: #4f46e5;
            color: white;
            padding: 20px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 11px;
            opacity: 0.9;
        }

        .container {
            padding: 0 20px 20px;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #4f46e5;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        .meta {
            background: #f9fafb;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .meta-row {
            display: flex;
            margin-bottom: 5px;
        }

        .meta-label {
            font-weight: bold;
            width: 120px;
            color: #6b7280;
        }

        .meta-value {
            flex: 1;
        }

        .content {
            white-space: pre-wrap;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #9ca3af;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background: #f9fafb;
            font-weight: bold;
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: bold;
        }

        .badge-green {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-amber {
            background: #fef3c7;
            color: #92400e;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ $report->title ?? ucfirst($report->type) . ' Report' }}</h1>
        <p>{{ $project->name }} • {{ $report->report_date->format('F j, Y') }}</p>
    </div>

    <div class="container">
        <div class="meta">
            <div class="meta-row">
                <span class="meta-label">Report Type:</span>
                <span class="meta-value">{{ ucfirst($report->type) }}</span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Report Date:</span>
                <span class="meta-value">{{ $report->report_date->format('F j, Y') }}</span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Created By:</span>
                <span class="meta-value">{{ $report->creator->name ?? 'Unknown' }}</span>
            </div>
            @if($project->client_name)
            <div class="meta-row">
                <span class="meta-label">Client:</span>
                <span class="meta-value">{{ $project->client_name }}</span>
            </div>
            @endif
        </div>

        <div class="section">
            <div class="section-title">Report Content</div>
            <div class="content">{{ $report->body ?? 'No content provided.' }}</div>
        </div>

        <div class="footer">
            Generated on {{ $generatedAt }} • BuildFlow
        </div>
    </div>
</body>

</html>