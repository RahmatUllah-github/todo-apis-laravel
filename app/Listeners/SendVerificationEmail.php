<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Mail\VerificationMail;
use Illuminate\Support\Facades\Mail;

class SendVerificationEmail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        $user = $event->user;
        $verificationCode = $event->verificationCode;

        // Send email containing verification code
        Mail::to($user['email'])->send(new VerificationMail($user['name'], $verificationCode));
    }
}
