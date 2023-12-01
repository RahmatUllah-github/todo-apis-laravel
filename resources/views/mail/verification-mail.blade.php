<x-mail::message>
<!-- Email Subject -->
# Verification Mail

<!-- Greeting -->
Hi {{ $userName }}! <br>

<!-- Registration Thank You Message (if applicable) -->
@if ($userRegistrationProcess)
    Thank you for registering with {{ config('app.name') }}. Use the verification code below to complete the process: <br>
@endif

<!-- Display Verification Code -->
## {{ $verificationCode }} <br>

<!-- Verification Instructions -->
Please enter this code in the app to verify your email. <br>

<!-- Non-Registration Disclaimer (if applicable) -->
@if ($userRegistrationProcess)
    If you didn't register on {{ config('app.name') }}, ignore this email. <br>
@endif

<!-- Closing Greeting -->
Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
