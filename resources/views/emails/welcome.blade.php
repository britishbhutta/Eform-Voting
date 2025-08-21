@component('mail::message')
# Hello {{ $firstName ?: 'there' }},

Thanks for signing up — please use the verification code below to activate your account.

@component('mail::panel')
**{{ $code }}**
@endcomponent

This code will expire in 2 minutes.

If you did not register on our site, just ignore this email.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
