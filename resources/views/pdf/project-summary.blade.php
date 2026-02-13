<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $project->name }} - Summary</title>
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
            font-size: 20px;
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
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #4f46e5;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 5px;
            margin-bottom: 12px;
        }

        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .stat-card {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 15px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #111827;
        }

        .stat-label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            margin-top: 5px;
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
            font-size: 11px;
        }

        th {
            background: #f9fafb;
            font-weight: bold;
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

        .badge-open {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-in_progress {
            background: #dbeafe;
            color: #1e40af;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #9ca3af;
            text-align: center;
        }

        .money {
            font-family: monospace;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ $project->name }}</h1>
        <p>Project Summary • Generated {{ $generatedAt }}</p>
    </div>

    <div class="container">
        <!-- Quick Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">{{ $project->status }}</div>
                <div class="stat-label">Status</div>
            </div>
            <div class="stat-card">
                <div class="stat-value money">₦{{ number_format($totalCosts, 2) }}</div>
                <div class="stat-label">Total Costs</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ count($openIssues) }}</div>
                <div class="stat-label">Open Issues</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ count($project->team ?? []) }}</div>
                <div class="stat-label">Team Members</div>
            </div>
        </div>

        <!-- Open Issues -->
        @if(count($openIssues) > 0)
        <div class="section">
            <div class="section-title">Open Issues</div>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Reported</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($openIssues as $issue)
                    <tr>
                        <td>{{ $issue->title }}</td>
                        <td>{{ ucfirst($issue->priority) }}</td>
                        <td><span class="badge badge-{{ $issue->status }}">{{ ucfirst(str_replace('_', ' ', $issue->status)) }}</span></td>
                        <td>{{ $issue->created_at->format('M j') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Recent Costs -->
        @if(count($recentCosts) > 0)
        <div class="section">
            <div class="section-title">Recent Costs (Last 10)</div>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Vendor</th>
                        <th style="text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentCosts as $cost)
                    <tr>
                        <td>{{ $cost->cost_date->format('M j') }}</td>
                        <td>{{ ucfirst($cost->category) }}</td>
                        <td>{{ $cost->vendor ?? '—' }}</td>
                        <td style="text-align: right;" class="money">₦{{ number_format($cost->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Recent Logs -->
        @if(count($recentLogs) > 0)
        <div class="section">
            <div class="section-title">Recent Activity Logs (Last 10)</div>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Title</th>
                        <th>By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentLogs as $log)
                    <tr>
                        <td>{{ $log->log_date->format('M j') }}</td>
                        <td>{{ ucfirst($log->type) }}</td>
                        <td>{{ $log->title ?? '—' }}</td>
                        <td>{{ $log->user->name ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <div class="footer">
            This summary was generated automatically by BuildFlow on {{ $generatedAt }}
        </div>
    </div>
</body>

</html>