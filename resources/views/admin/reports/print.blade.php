<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ request('mode') === 'pdf' ? 'Barangay_Banlic_CCIRS_Report_' . now()->format('Y-m-d') . '.pdf' : 'Barangay Banlic CCIRS - Incident & Complaint Report' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #1c2340;
            background: #fff;
            margin: 0;
            padding: 24px;
            font-size: 13px;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #141f5e;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 20px;
            margin: 0 0 4px;
            text-transform: uppercase;
            color: #141f5e;
        }
        .header h2 {
            font-size: 15px;
            margin: 0 0 4px;
            font-weight: normal;
        }
        .header p {
            margin: 0;
            font-size: 12px;
            color: #555;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 18px;
            font-size: 12px;
        }
        .meta-table td {
            padding: 4px 0;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        table.data th, table.data td {
            border: 1px solid #ccc;
            padding: 7px 9px;
            text-align: left;
        }
        table.data th {
            background: #f0f3fa;
            font-weight: bold;
            font-size: 11.5px;
            text-transform: uppercase;
        }
        .footer {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }
        .sign-box {
            text-align: center;
            width: 200px;
        }
        .sign-line {
            border-bottom: 1px solid #000;
            margin-top: 45px;
            margin-bottom: 4px;
        }
        .no-print {
            margin-bottom: 20px;
            padding: 12px 16px;
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        .btn {
            background: #141f5e;
            color: #fff;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn:hover {
            background: #1e296e;
        }
        .btn-secondary-print {
            background: #fff;
            color: #141f5e;
            border: 1px solid #c7d2fe;
        }
        .btn-secondary-print:hover {
            background: #e0e7ff;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
                background: #fff;
            }
            @page {
                size: portrait;
                margin: 12mm 10mm;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <div style="display:flex; align-items:center; gap:8px;">
            @if (request('mode') === 'pdf')
                <div><strong>PDF Export:</strong> In the browser prompt, select <strong>"Save as PDF"</strong> as the destination to download your report.</div>
            @else
                <div><strong>Print Preview:</strong> You can send this official report directly to your printer or save it as a PDF.</div>
            @endif
        </div>
        <div style="display:flex; gap:8px;">
            <button class="btn" type="button" onclick="window.print()">
                {{ request('mode') === 'pdf' ? 'Save as PDF' : 'Print Now' }}
            </button>
            <button class="btn btn-secondary-print" type="button" onclick="window.close()">Close</button>
        </div>
    </div>

    <div class="header" style="display: flex; align-items: center; justify-content: center; gap: 20px; text-align: center; border-bottom: 2px solid #141f5e; padding-bottom: 16px; margin-bottom: 20px;">
        <img src="{{ asset('images/logo.png') }}" alt="Barangay Banlic Seal" style="width: 76px; height: 76px; object-fit: contain; flex-shrink: 0;">
        <div style="flex: 1; max-width: 650px;">
            <h2 style="font-size: 13px; margin: 0 0 2px; text-transform: uppercase; letter-spacing: 0.5px; color: #475569; font-weight: 600;">Republic of the Philippines &bull; Province of Laguna &bull; City of Cabuyao</h2>
            <h1 style="font-size: 24px; margin: 0 0 3px; font-weight: 800; color: #141f5e; text-transform: uppercase; letter-spacing: 1px;">BARANGAY BANLIC</h1>
            <p style="font-size: 12px; margin: 0 0 4px; font-weight: 600; color: #334155;">Community Complaint &amp; Incident Reporting System (CCIRS)</p>
            <p style="font-size: 11.5px; margin: 0; text-transform: uppercase; letter-spacing: 0.8px; color: #1e3a8a; font-weight: 700;">OFFICIAL INCIDENTS AND COMPLAINTS SUMMARY REPORT</p>
        </div>
        <img src="{{ asset('images/logo.png') }}" alt="Barangay Banlic Seal" style="width: 76px; height: 76px; object-fit: contain; flex-shrink: 0;">
    </div>

    <div style="overflow-x: auto;">
        <table class="meta-table">
            <tr>
                <td><strong>Generated By:</strong> {{ auth()->user()->full_name }} ({{ auth()->user()->email }})</td>
                <td style="text-align:right;"><strong>Date Generated:</strong> {{ $generatedAt->format('F d, Y h:i A') }}</td>
            </tr>
            <tr>
                <td><strong>Total Filtered Records:</strong> {{ $complaints->count() }}</td>
                <td style="text-align:right;"><strong>Status Filter:</strong> {{ !empty($filters['status_id']) ? 'Applied' : 'All' }}</td>
            </tr>
        </table>
    </div>

    <div style="overflow-x: auto;">
        <table class="data">
        <thead>
            <tr>
                <th style="width: 14%;">Reference No.</th>
                <th>Title / Incident</th>
                <th>Complainant</th>
                <th>Category</th>
                <th>Area / Purok</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Date Filed</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($complaints as $c)
            <tr>
                <td><strong>{{ $c->reference_no }}</strong></td>
                <td>{{ $c->title }}</td>
                <td>{{ $c->complainant ? $c->complainant->full_name : 'N/A' }}</td>
                <td>{{ $c->category ? $c->category->category_name : '—' }}</td>
                <td>{{ $c->area ? $c->area->area_name : '—' }}</td>
                <td>{{ $c->priority ? $c->priority->priority_name : '—' }}</td>
                <td>{{ $c->status ? $c->status->status_name : '—' }}</td>
                <td>{{ $c->created_at ? $c->created_at->format('M d, Y') : '—' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" style="text-align:center; padding: 20px;">No complaints match the selected filters.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    </div>

    <div class="footer">
        <div class="sign-box">
            <div class="sign-line"></div>
            <div>Prepared By</div>
            <div style="font-size:11px; color:#555;">Barangay Staff</div>
        </div>
        <div class="sign-box">
            <div class="sign-line"></div>
            <div>Noted By / Approved By</div>
            <div style="font-size:11px; color:#555;">Punong Barangay / Secretary</div>
        </div>
    </div>

    @if (request('autoprint') || request('mode') === 'pdf')
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 350);
        });
    </script>
    @endif
</body>
</html>
