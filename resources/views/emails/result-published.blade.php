@component('mail::message')

# New Results Published!

Dear **{{ $studentName }}**,

Your results for **{{ $semester }}** semester have been published!

## Your Result
- **SGPA:** {{ number_format($sgpa, 2) }}

Log in to your student portal to view the detailed breakdown of your grades.

@component('mail::button', ['url' => config('app.client_url', 'http://localhost:5173') . '/student-corner/results'])
View Results
@endcomponent

Best regards,
**CMPI Administration**

@endcomponent
