<x-mail::message>
# Application Received

Dear **{{ $admission->name }}**,

Thank you for applying to Cox's Bazar Model Polytechnic Institute. We have received your admission application.

## Application Details

| Field | Value |
|-------|-------|
| **Application ID** | {{ $admission->application_id }} |
| **Department** | {{ $admission->department }} |
| **Status** | {{ $admission->status }} |

We will review your application and get back to you soon.

<x-mail::button :url="'https://www.cmpi.edu.bd'">
Visit Website
</x-mail::button>

Regards,<br>
{{ config('app.name') }}
</x-mail::message>
