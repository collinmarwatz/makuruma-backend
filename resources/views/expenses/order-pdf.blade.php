<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #1f2937; font-size: 10px; }
        .header { display: table; width: 100%; margin-bottom: 6px; }
        .logo-box { display: table-cell; width: 90px; vertical-align: middle; }
        .logo-img { height: 50px; }
        .company-info { display: table-cell; vertical-align: middle; padding-left: 10px; }
        .company-name { font-size: 18px; font-weight: bold; color: #111827; }
        .company-address { font-size: 10px; color: #374151; }
        .section-title { text-align: center; font-size: 13px; font-weight: bold; margin: 16px 0 8px; text-decoration: underline; }
        .info-grid { display: table; width: 100%; margin-bottom: 14px; }
        .info-cell { display: table-cell; width: 25%; padding: 4px 0; font-size: 10px; }
        .info-label { color: #6b7280; font-size: 8px; text-transform: uppercase; }
        .info-value { font-weight: bold; color: #111827; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th { background: #eef2ff; color: #111827; text-align: left; padding: 5px 4px; font-size: 8px; border: 1px solid #9ca3af; text-transform: uppercase; }
        td { padding: 5px 4px; border: 1px solid #9ca3af; font-size: 9px; }
        .total-row td { font-weight: bold; background: #f9fafb; }
        .status-badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-approved { background: #dbeafe; color: #1e40af; }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .note { font-size: 9px; color: #6b7280; margin-bottom: 10px; }
        .audit-trail { margin-top: 16px; font-size: 9px; color: #4b5563; }
        .audit-trail div { margin-bottom: 3px; }
        .footer { margin-top: 20px; font-size: 8px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-box"><img src="{{ $logoBase64 }}" class="logo-img" alt="Makuruma Logistics"></div>
        <div class="company-info">
            <div class="company-name">MAKURUMA LOGISTICS LIMITED</div>
            <div class="company-address">P.O.Box 31902 Dar es salaam-Tanzania, Tel:+255 710 001100, +255 713 013132.</div>
        </div>
    </div>

    <div class="section-title">EXPENSE ORDER — {{ $expense->order_number }}</div>

    <div class="info-grid">
        <div class="info-cell">
            <div class="info-label">Category</div>
            <div class="info-value">{{ ucfirst($expense->category) }}</div>
        </div>
        <div class="info-cell">
            <div class="info-label">Reference</div>
            <div class="info-value">{{ $expense->trip->trip_number ?? $expense->truck->reg_no ?? '—' }}</div>
        </div>
        <div class="info-cell">
            <div class="info-label">Created By</div>
            <div class="info-value">{{ $expense->creator->name }}</div>
        </div>
        <div class="info-cell">
            <div class="info-label">Status</div>
            <div class="info-value"><span class="status-badge status-{{ $expense->status }}">{{ strtoupper($expense->status) }}</span></div>
        </div>
    </div>

    @if ($expense->trucks->count() > 0)
        <div class="section-title" style="margin-top:10px;">Trucks Covered by This Order</div>
        <table>
            <thead>
                <tr><th>No.</th><th>Truck</th></tr>
            </thead>
            <tbody>
                @foreach ($expense->trucks as $index => $truck)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $truck->reg_no }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p class="note">Amount below is per truck × {{ $expense->trucks->count() }} truck(s).</p>
    @endif

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align:right;">Amount ($)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($expense->lines as $line)
                <tr>
                    <td>{{ $line->description }}</td>
                    <td style="text-align:right;">{{ number_format($line->amount, 2) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td>Total {{ $expense->trucks->count() > 1 ? '(× ' . $expense->trucks->count() . ' trucks)' : '' }}</td>
                <td style="text-align:right;">{{ number_format($expense->total_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="audit-trail">
        <div><strong>Created:</strong> {{ $expense->creator->name }} on {{ \Carbon\Carbon::parse($expense->created_at)->format('d M Y, H:i') }}</div>
        @if ($expense->approver)
            <div><strong>{{ $expense->status === 'rejected' ? 'Rejected' : 'Approved' }}:</strong> {{ $expense->approver->name }} on {{ \Carbon\Carbon::parse($expense->approved_at)->format('d M Y, H:i') }}</div>
        @endif
        @if ($expense->payer)
            <div><strong>Paid:</strong> {{ $expense->payer->name }} on {{ \Carbon\Carbon::parse($expense->paid_at)->format('d M Y, H:i') }}</div>
        @endif
    </div>

    <div class="footer">Generated by Makuruma Logistics Management System · {{ now()->format('d M Y, H:i') }}</div>
</body>
</html>