<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt - Ref #{{ $bill->id }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1a1a1a; margin: 25px; line-height: 1.4; }
        .logo-section { width: 100%; border-bottom: 2px solid #16a34a; padding-bottom: 12px; margin-bottom: 20px; }
        .logo-table { width: 100%; border-collapse: collapse; }
        .logo-table td { padding: 0; border: none; vertical-align: middle; }
        .logo-image { height: 60px; }
        .institute-title { font-size: 16px; font-weight: bold; color: #16a34a; margin: 0; text-transform: uppercase; }
        .institute-subtitle { font-size: 10px; color: #555; margin: 2px 0 0; }
        
        .receipt-header { text-align: center; margin-bottom: 20px; }
        .receipt-title { font-size: 14px; font-weight: bold; color: #222; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid #1a1a1a; display: inline-block; padding: 4px 15px; }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 5px 8px; border: 1px solid #ddd; font-size: 10px; }
        .info-table td.label { font-weight: bold; background-color: #f8fafc; color: #374151; width: 120px; }

        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .details-table th { background-color: #16a34a; color: white; padding: 8px; text-align: left; font-size: 9px; text-transform: uppercase; }
        .details-table td { padding: 8px; border: 1px solid #ddd; font-size: 10px; }
        .details-table tr.total-row td { font-weight: bold; background-color: #f0fdf4; border-top: 2px solid #16a34a; font-size: 11px; }

        .status-container { margin-top: 15px; text-align: right; }
        .status-badge { display: inline-block; border: 3px double #16a34a; color: #16a34a; font-size: 14px; font-weight: bold; padding: 6px 15px; text-transform: uppercase; letter-spacing: 1px; transform: rotate(-5deg); margin-right: 20px; }

        .footer-table { width: 100%; border-collapse: collapse; margin-top: 50px; }
        .footer-table td { border: none; padding: 0; vertical-align: bottom; }
        .signature-line { border-top: 1px solid #666; width: 180px; text-align: center; font-size: 9px; padding-top: 4px; color: #4b5563; }

        .sys-info { margin-top: 60px; font-size: 8px; color: #9ca3af; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="logo-section">
        <table class="logo-table">
            <tr>
                @if($logoSrc)
                    <td style="width: 70px;">
                        <img src="{{ $logoSrc }}" class="logo-image" alt="CMPI Logo">
                    </td>
                @endif
                <td>
                    <h1 class="institute-title">Cox's Bazar Model Polytechnic Institute</h1>
                    <p class="institute-subtitle">Official Student Payment Receipt / Challan Copy</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="receipt-header">
        <div class="receipt-title">Payment Receipt</div>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Receipt Number:</td>
            <td><strong>REC-{{ $bill->id }}-{{ sprintf('%04d', $user->id) }}</strong></td>
            <td class="label">Payment Date:</td>
            <td>{{ $bill->paid_at ? \Carbon\Carbon::parse($bill->paid_at)->format('d M Y, h:i A') : '—' }}</td>
        </tr>
        <tr>
            <td class="label">Student Name:</td>
            <td>{{ $user->name }}</td>
            <td class="label">Student ID:</td>
            <td>{{ $user->student_id ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Department:</td>
            <td>{{ $user->department ?? '—' }}</td>
            <td class="label">Semester / Session:</td>
            <td>{{ $user->semester ?? '—' }} / {{ $user->session ?? '—' }}</td>
        </tr>
    </table>

    <table class="details-table">
        <thead>
            <tr>
                <th style="width: 60%;">Description</th>
                <th style="width: 20%; text-align: center;">Payment Method</th>
                <th style="width: 20%; text-align: right;">Amount Paid</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>{{ $bill->title ?? $bill->type }}</strong>
                    @if($bill->description)
                        <p style="margin: 4px 0 0; font-size: 8px; color: #6b7280; font-weight: normal;">{{ $bill->description }}</p>
                    @endif
                    @if($bill->transaction_id)
                        <p style="margin: 2px 0 0; font-size: 8px; color: #6b7280; font-weight: normal;">Transaction ID: {{ $bill->transaction_id }}</p>
                    @endif
                </td>
                <td style="text-align: center; text-transform: uppercase;">{{ $bill->payment_method ?? 'Cash/Office' }}</td>
                <td style="text-align: right;">৳{{ number_format($bill->amount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="2" style="text-align: right;">Total Amount:</td>
                <td style="text-align: right;">৳{{ number_format($bill->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <tr>
            <td style="width: 50%; vertical-align: top; border: none; padding: 0;">
                <div class="status-container" style="text-align: left;">
                    <div class="status-badge">PAID</div>
                </div>
            </td>
            <td style="width: 50%; vertical-align: bottom; border: none; padding: 0; text-align: right;">
                <table class="footer-table" style="float: right;">
                    <tr>
                        <td>
                            <div class="signature-line">
                                Authorized Accounts Officer
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="sys-info">
        <p>Cox's Bazar Model Polytechnic Institute — Online Billing Portal</p>
        <p>This document is generated dynamically from the institutional accounts records. Downloaded on: {{ $downloaded_at }}</p>
    </div>
</body>
</html>
