@component('mail::message')

# Admission Approved!

Dear **{{ $admission->name }}**,

Congratulations! Your admission application to CMPI has been **approved**.

## Your Details
- **Name:** {{ $admission->name }}
- **Department:** {{ $admission->department }}
- **Session:** {{ $admission->session }}
@if($studentId)
- **Student ID:** {{ $studentId }}
@endif

You can now log in to your student portal to access courses, results, and other services.

@component('mail::button', ['url' => config('app.client_url', 'http://localhost:5173')])
Go to Student Portal
@endcomponent

If you have any questions, please don't hesitate to contact us.

Best regards,
**CMPI Administration**

@endcomponent
