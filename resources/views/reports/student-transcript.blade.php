<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Transcript - Roll {{ $roll }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a1a1a; margin: 20px; }
        .header { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; margin: 0; color: #2563eb; }
        .header h2 { font-size: 14px; margin: 5px 0 0; color: #555; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 9px; color: #666; }
        .student-info { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 4px; padding: 12px; margin-bottom: 15px; }
        .student-info .row { display: flex; gap: 30px; margin-bottom: 5px; }
        .student-info .label { font-weight: bold; color: #1e40af; min-width: 100px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #2563eb; color: white; padding: 6px 4px; text-align: left; font-size: 8px; text-transform: uppercase; }
        td { padding: 5px 4px; border-bottom: 1px solid #e5e5e5; font-size: 9px; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .summary { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 4px; padding: 10px; margin: 15px 0; }
        .summary-grid { display: flex; gap: 20px; }
        .summary-item { text-align: center; }
        .summary-item .value { font-size: 16px; font-weight: bold; color: #16a34a; }
        .summary-item .label { font-size: 8px; color: #666; }
        .footer { margin-top: 20px; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #e5e5e5; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CMPI — Student Academic Transcript</h1>
        <h2>Board Examination Results</h2>
    </div>

    <div class="meta">
        <span>Roll Number: {{ $roll }}</span>
        <span>Generated: {{ $generatedAt }}</span>
    </div>

    @if($student)
    <div class="student-info">
        <div class="row"><span class="label">Name:</span> {{ $student->name }}</div>
        <div class="row"><span class="label">Student ID:</span> {{ $student->student_id }}</div>
        <div class="row"><span class="label">Department:</span> {{ $student->department }}</div>
        <div class="row"><span class="label">Session:</span> {{ $student->session }}</div>
    </div>
    @endif

    @php
        $totalGpa = $results->where('gpa', '>', 0)->avg('gpa');
        $highestGpa = $results->where('gpa', '>', 0)->max('gpa');
        $lowestGpa = $results->where('gpa', '>', 0)->min('gpa');
    @endphp

    <div class="summary">
        <div class="summary-grid">
            <div class="summary-item">
                <div class="value">{{ $results->pluck('semester')->unique()->count() }}</div>
                <div class="label">Semesters</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $totalGpa ? number_format($totalGpa, 2) : '—' }}</div>
                <div class="label">Average GPA</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $highestGpa }}</div>
                <div class="label">Highest GPA</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $lowestGpa }}</div>
                <div class="label">Lowest GPA</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Semester</th>
                <th>Regulation</th>
                <th>GPA</th>
                <th>Department</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results as $result)
                <tr>
                    <td>{{ $result->semester }}</td>
                    <td>{{ $result->regulation ?? '—' }}</td>
                    <td><strong>{{ $result->gpa ? number_format($result->gpa, 2) : '—' }}</strong></td>
                    <td>{{ $result->department ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Cox's Bazar Model Polytechnic Institute — Official Academic Transcript</p>
        <p>This is a computer-generated document. No signature required.</p>
    </div>
</body>
</html>
