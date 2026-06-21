@component('mail::message')

# Admission Application Update

Dear **{{ $admission->name }}**,

Thank you for applying to CMPI. After careful review, we regret to inform you that your admission application has **not been approved** at this time.

## Your Details
- **Name:** {{ $admission->name }}
- **Department:** {{ $admission->department }}
- **Session:** {{ $admission->session }}

@if($reason)
## Reason
{{ $reason }}
@endif

You may reapply for the next session or contact the admissions office for more information.

@component('mail::button', ['url' => config('app.client_url', 'http://localhost:5173')])
Contact Admissions
@endcomponent

Thank you for your interest in CMPI.

Best regards,
**CMPI Administration**

@endcomponent
