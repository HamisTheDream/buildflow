<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1f2937;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        /* Header with Dark Slate Background */
        .header {
            background: linear-gradient(to right, #0f172a, #1e293b);
            /* slate-900 to slate-800 */
            color: #fff;
            padding: 20px 24px;
            margin: -10px -10px 20px -10px;
            border-bottom: 4px solid #f97316;
            /* brand-500 */
        }

        .header-top {
            display: table;
            width: 100%;
            margin-bottom: 12px;
        }

        .header-top>div {
            display: table-cell;
            vertical-align: middle;
        }

        .logo {
            font-size: 22px;
            font-weight: 700;
            color: #fff;
        }

        .logo-icon {
            display: inline-block;
            width: 28px;
            height: 28px;
            background: linear-gradient(135deg, #f97316, #ea580c);
            border-radius: 6px;
            text-align: center;
            line-height: 28px;
            font-size: 16px;
            color: #fff;
            margin-right: 8px;
            vertical-align: middle;
        }

        .logo-text {
            vertical-align: middle;
        }

        .header-right {
            text-align: right;
            color: #94a3b8;
            /* slate-400 */
            font-size: 10px;
        }

        .report-title {
            font-size: 20px;
            font-weight: 700;
            color: #f97316;
            /* brand-500 */
            margin: 0;
        }

        .report-subtitle {
            font-size: 12px;
            color: #cbd5e1;
            /* slate-300 */
            margin-top: 4px;
        }

        .report-meta {
            color: #94a3b8;
            font-size: 10px;
            margin-top: 8px;
        }

        /* Section Styling */
        .section {
            margin-top: 24px;
            page-break-inside: avoid;
        }

        .section h2 {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            /* slate-900 */
            margin: 0 0 12px 0;
            padding: 8px 12px;
            background: linear-gradient(to right, #f1f5f9, #fff);
            /* slate-100 fade */
            border-left: 4px solid #f97316;
            /* brand-500 */
            border-radius: 0 6px 6px 0;
        }

        .item {
            border: 1px solid #e2e8f0;
            /* slate-200 */
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 10px;
            background: #fff;
            page-break-inside: avoid;
        }

        .muted {
            color: #64748b;
            /* slate-500 */
            font-size: 10px;
        }

        /* Layout Utilities */
        .row {
            display: table;
            width: 100%;
            margin-bottom: 6px;
        }

        .row>div {
            display: table-cell;
            vertical-align: top;
        }

        .row>div:last-child {
            text-align: right;
            width: 1%;
            white-space: nowrap;
            padding-left: 12px;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: auto;
            margin-bottom: 12px;
        }

        tr {
            page-break-inside: avoid;
        }

        th,
        td {
            padding: 10px 8px;
            text-align: left;
            vertical-align: top;
            font-size: 10px;
        }

        th {
            background: #0f172a;
            /* slate-900 */
            color: #fff;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.08em;
        }

        th:first-child {
            border-radius: 6px 0 0 0;
        }

        th:last-child {
            border-radius: 0 6px 0 0;
        }

        td {
            border-bottom: 1px solid #f1f5f9;
            /* slate-100 */
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:nth-child(even) td {
            background: #f8fafc;
            /* slate-50 */
        }

        /* Utilities */
        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .font-bold {
            font-weight: 700;
        }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: 600;
        }

        .badge-default {
            background: #f1f5f9;
            color: #475569;
        }

        .badge-orange {
            background: #fff7ed;
            color: #c2410c;
            border: 1px solid #fed7aa;
        }

        .badge-blue {
            background: #eff6ff;
            color: #1e40af;
            border: 1px solid #dbeafe;
        }

        .badge-green {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #dcfce7;
        }

        .badge-red {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fee2e2;
        }

        .badge-purple {
            background: #faf5ff;
            color: #6b21a8;
            border: 1px solid #e9d5ff;
        }

        .total {
            font-weight: 700;
            font-size: 11px;
            color: #0f172a;
            background: #f1f5f9 !important;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            padding: 16px 24px;
            background: #0f172a;
            color: #94a3b8;
            font-size: 9px;
            border-top: 3px solid #f97316;
            margin: 40px -10px -10px -10px;
        }

        .footer-content {
            display: table;
            width: 100%;
        }

        .footer-content>div {
            display: table-cell;
            vertical-align: middle;
        }

        .footer-left {
            color: #f97316;
            font-weight: 700;
            font-size: 12px;
        }

        .footer-right {
            text-align: right;
        }

        /* Summary Box */
        .summary-box {
            background: linear-gradient(135deg, #0f172a, #1e3a5f);
            color: #fff;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .summary-box h3 {
            color: #f97316;
            font-size: 12px;
            margin: 0 0 8px 0;
        }

        .summary-grid {
            display: table;
            width: 100%;
        }

        .summary-stat {
            display: table-cell;
            text-align: center;
            padding: 8px;
        }

        .summary-stat .number {
            font-size: 24px;
            font-weight: 700;
            color: #f97316;
        }

        .summary-stat .label {
            font-size: 9px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
    </style>
</head>

<body>
    {{-- Professional Header --}}
    <div class="header">
        <div class="header-top">
            <div>
                <span class="logo">
                    <span class="logo-icon">▲</span>
                    <span class="logo-text">BuildFlow</span>
                </span>
            </div>
            <div class="header-right">
                Generated: {{ $generatedAt }}<br>
                By: {{ $generatedBy }}
            </div>
        </div>
        <div class="report-title">{{ $project->name }}</div>
        <div class="report-subtitle">{{ $subtitle ?? 'Project Report' }}</div>
        <div class="report-meta">
            Report Period: {{ $from }} to {{ $to }}
        </div>
    </div>

    {{-- Quick Summary Box --}}
    <div class="summary-box">
        <h3>Report Summary</h3>
        <div class="summary-grid">
            <div class="summary-stat">
                <div class="number">{{ count($todayLogs) }}</div>
                <div class="label">Today Logs</div>
            </div>
            <div class="summary-stat">
                <div class="number">{{ count($tasks) }}</div>
                <div class="label">Tasks</div>
            </div>
            <div class="summary-stat">
                <div class="number">{{ count($issues) }}</div>
                <div class="label">Issues</div>
            </div>
            <div class="summary-stat">
                <div class="number">${{ number_format(collect($costs)->sum('amount'), 0) }}</div>
                <div class="label">Total Costs</div>
            </div>
        </div>
    </div>

    {{-- Today Logs --}}
    @if($options['include_today_logs'] ?? true)
    <div class="section">
        <h2>Today Logs</h2>
        @forelse($todayLogs as $log)
        <div class="item">
            <div class="row">
                <div>
                    <strong>{{ $log['user']['name'] }}</strong>
                    <span class="muted" style="margin-left: 8px;">{{ $log['log_date'] }}</span>
                </div>
                <div>
                    <span class="badge badge-orange">{{ $log['progress_percent'] ?? '—' }}% Complete</span>
                </div>
            </div>

            <div class="muted" style="margin-top: 4px;">Weather: {{ $log['weather'] ?? '—' }}</div>

            <div style="margin-top: 10px;">
                <strong style="color: #0f172a;">Work Completed:</strong><br>
                <span style="color: #475569;">{!! nl2br(e($log['work_done'] ?? '—')) !!}</span>
            </div>

            @if(!empty($log['blockers']))
            <div style="margin-top: 8px;">
                <strong style="color: #991b1b;">Blockers:</strong><br>
                <span style="color: #475569;">{!! nl2br(e($log['blockers'])) !!}</span>
            </div>
            @endif

            @if(!empty($log['next_steps']))
            <div style="margin-top: 8px;">
                <strong style="color: #166534;">Next Steps:</strong><br>
                <span style="color: #475569;">{!! nl2br(e($log['next_steps'])) !!}</span>
            </div>
            @endif
        </div>
        @empty
        <div class="muted" style="padding: 12px;">No today logs recorded in this period.</div>
        @endforelse
    </div>
    @endif

    {{-- General Logs --}}
    @if($options['include_logs'] ?? true)
    <div class="section">
        <h2>Activity Timeline</h2>
        @forelse($logs as $l)
        <div class="item">
            <div class="row">
                <div>
                    <strong style="color: #0f172a;">{{ $l['title'] ?: '(No title)' }}</strong>
                    <span class="badge badge-blue" style="margin-left: 6px;">{{ $l['type'] }}</span>
                </div>
                <div>
                    <span class="muted">{{ $l['log_date'] }} {{ $l['log_time'] }}</span>
                </div>
            </div>
            <div class="muted" style="margin: 4px 0 8px 0;">By {{ $l['user']['name'] }}</div>
            <div style="color: #475569;">{!! nl2br(e($l['body'])) !!}</div>
        </div>
        @empty
        <div class="muted" style="padding: 12px;">No activity logs in this period.</div>
        @endforelse
    </div>
    @endif

    {{-- Tasks --}}
    @if($options['include_tasks'] ?? true)
    <div class="section">
        <h2>Task Progress</h2>
        @if(count($tasks) > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 40%;">Task</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Assignee</th>
                    <th>Due Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tasks as $t)
                <tr>
                    <td><strong>{{ $t['title'] }}</strong></td>
                    <td>
                        @if($t['status'] === 'completed')
                        <span class="badge badge-green">{{ $t['status'] }}</span>
                        @elseif($t['status'] === 'in_progress')
                        <span class="badge badge-blue">{{ $t['status'] }}</span>
                        @else
                        <span class="badge badge-default">{{ $t['status'] }}</span>
                        @endif
                    </td>
                    <td>
                        @if($t['priority'] === 'high' || $t['priority'] === 'urgent')
                        <span class="badge badge-red">{{ $t['priority'] }}</span>
                        @else
                        <span class="badge badge-default">{{ $t['priority'] }}</span>
                        @endif
                    </td>
                    <td>{{ $t['assignee']['name'] ?? '—' }}</td>
                    <td>{{ $t['due_date'] ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="muted" style="padding: 12px;">No tasks in this project.</div>
        @endif
    </div>
    @endif

    {{-- Issues --}}
    @if($options['include_issues'] ?? true)
    <div class="section">
        <h2>Issues & Blockers</h2>
        @if(count($issues) > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 40%;">Issue</th>
                    <th>Status</th>
                    <th>Severity</th>
                    <th>Assignee</th>
                    <th>Due Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($issues as $i)
                <tr>
                    <td><strong>{{ $i['title'] }}</strong></td>
                    <td>
                        @if($i['status'] === 'resolved')
                        <span class="badge badge-green">{{ $i['status'] }}</span>
                        @elseif($i['status'] === 'open')
                        <span class="badge badge-red">{{ $i['status'] }}</span>
                        @else
                        <span class="badge badge-default">{{ $i['status'] }}</span>
                        @endif
                    </td>
                    <td>
                        @if($i['severity'] === 'critical' || $i['severity'] === 'high')
                        <span class="badge badge-red">{{ $i['severity'] }}</span>
                        @elseif($i['severity'] === 'medium')
                        <span class="badge badge-orange">{{ $i['severity'] }}</span>
                        @else
                        <span class="badge badge-default">{{ $i['severity'] }}</span>
                        @endif
                    </td>
                    <td>{{ $i['assignee']['name'] ?? '—' }}</td>
                    <td>{{ $i['due_date'] ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="muted" style="padding: 12px;">No issues recorded.</div>
        @endif
    </div>
    @endif

    {{-- Costs --}}
    @if($options['include_costs'] ?? true)
    <div class="section">
        <h2>Financial Summary</h2>
        @if(count($costs) > 0)
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th style="width: 35%;">Description</th>
                    <th>Vendor</th>
                    <th class="right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp
                @foreach($costs as $c)
                @php $total += $c['amount']; @endphp
                <tr>
                    <td>{{ $c['cost_date'] }}</td>
                    <td><span class="badge badge-purple">{{ $c['category'] ?? 'General' }}</span></td>
                    <td>{{ $c['description'] }}</td>
                    <td>{{ $c['vendor'] ?? '—' }}</td>
                    <td class="right"><strong>${{ number_format($c['amount'], 2) }}</strong></td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="4" class="right total">Total Expenditure</td>
                    <td class="right total" style="color: #f97316; font-size: 14px;">${{ number_format($total, 2) }}</td>
                </tr>
            </tbody>
        </table>
        @else
        <div class="muted" style="padding: 12px;">No costs recorded in this period.</div>
        @endif
    </div>
    @endif

    {{-- Attachments & Media --}}
    @if(count($media) > 0 || count($attachments) > 0)
    <div class="section">
        <h2>Evidence & Attachments</h2>

        @if(count($media) > 0)
        <div style="margin-bottom: 16px;">
            <strong style="color: #0f172a; font-size: 11px;">Media Files ({{ count($media) }})</strong>
            <ul style="margin: 8px 0; padding-left: 20px;">
                @foreach($media as $m)
                <li style="margin-bottom: 6px;">
                    {{ $m['caption'] ?: 'Media #' . $m['id'] }}
                    <span class="muted">({{ $m['created_at'] }})</span>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(count($attachments) > 0)
        <table>
            <thead>
                <tr>
                    <th>File Name</th>
                    <th>Caption</th>
                    <th>Size</th>
                    <th>Uploaded By</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attachments as $a)
                <tr>
                    <td><strong>{{ $a['original_name'] }}</strong></td>
                    <td>{{ $a['caption'] ?? '—' }}</td>
                    <td>{{ number_format($a['size'] / 1024, 1) }} KB</td>
                    <td>{{ $a['uploader'] ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @endif

    {{-- Professional Footer --}}
    <div class="footer">
        <div class="footer-content">
            <div class="footer-left">
                BuildFlow
            </div>
            <div class="footer-right">
                Simple construction tracking • www.buildflow.com<br>
                This report was automatically generated. For questions, contact your project manager.
            </div>
        </div>
    </div>
</body>

</html>