<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Department Result Report - {{ $department }} - {{ $semester }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a1a1a; margin: 20px; }
        .header { text-align: center; border-bottom: 2px solid #16a34a; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; margin: 0; color: #16a34a; }
        .header h2 { font-size: 14px; margin: 5px 0 0; color: #555; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 9px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #16a34a; color: white; padding: 6px 4px; text-align: left; font-size: 8px; text-transform: uppercase; }
        td { padding: 5px 4px; border-bottom: 1px solid #e5e5e5; font-size: 9px; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .pass { color: #16a34a; font-weight: bold; }
        .fail { color: #dc2626; font-weight: bold; }
        .footer { margin-top: 20px; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #e5e5e5; padding-top: 10px; }
        .summary { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 4px; padding: 10px; margin-bottom: 15px; }
        .summary-grid { display: flex; gap: 20px; }
        .summary-item { text-align: center; }
        .summary-item .value { font-size: 16px; font-weight: bold; color: #16a34a; }
        .summary-item .label { font-size: 8px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CMPI — Department Result Report</h1>
        <h2>{{ $department }} | {{ $semester }} Semester</h2>
    </div>

    <div class="meta">
        <span>Generated: {{ $generatedAt }}</span>
        <span>Total Records: {{ $results->count() }}</span>
    </div>

    @php
        $uniqueRolls = $results->pluck('roll')->unique();
        $totalStudents = $uniqueRolls->count();
        $passRolls = $results->where('gpa', '>=', 2.00)->pluck('roll')->unique();
        $passCount = $passRolls->count();
        $failCount = $totalStudents - $passCount;
        $avgGpa = $results->where('gpa', '>', 0)->avg('gpa');
    @endphp

    <div class="summary">
        <div class="summary-grid">
            <div class="summary-item">
                <div class="value">{{ $totalStudents }}</div>
                <div class="label">Total Students</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $passCount }}</div>
                <div class="label">Passed</div>
            </div>
            <div class="summary-item">
                <div class="value" style="color: #dc2626;">{{ $failCount }}</div>
                <div class="label">Failed</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $avgGpa ? number_format($avgGpa, 2) : '—' }}</div>
                <div class="label">Average GPA</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $totalStudents > 0 ? number_format(($passCount / $totalStudents) * 100, 1) : 0 }}%</div>
                <div class="label">Pass Rate</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Roll</th>
                <th>Name</th>
                <th>Regulation</th>
                <th>GPA</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results as $i => $result)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $result->roll }}</td>
                    <td>{{ $result->name ?? '—' }}</td>
                    <td>{{ $result->regulation ?? '—' }}</td>
                    <td>{{ $result->gpa ? number_format($result->gpa, 2) : '—' }}</td>
                    <td class="{{ $result->gpa >= 2.00 ? 'pass' : 'fail' }}">
                        {{ $result->gpa >= 2.00 ? 'PASS' : 'FAIL' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Cox's Bazar Model Polytechnic Institute — Official Result Report</p>
        <p>This is a computer-generated document. No signature required.</p>
    </div>
</body>
</html>
