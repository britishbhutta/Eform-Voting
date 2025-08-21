<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $code;
    public string $firstName;

    /**
     * Create a new message instance.
     */
    public function __construct(string $code, string $firstName = '')
    {
        $this->code = $code;
        $this->firstName = $firstName;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this
            ->subject('Your verification code')
            ->markdown('emails.welcome')
            ->with([
                'code' => $this->code,
                'firstName' => $this->firstName,
            ]);
    }
}
