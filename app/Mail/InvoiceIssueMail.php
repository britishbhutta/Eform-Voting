<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceIssueMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $booking, $tariff)
    {
        $this->user = $user;
        $this->booking = $booking;
        $this->tariff = $tariff;
    }

    public function build()
    {
        return $this
            ->subject('Invoice Tariff Purchase')
            ->markdown('emails.invoiceTariffPurchase')
            ->with([
                'user' => $this->user,
                'booking' => $this->booking,
                'tariff' => $this->tariff,
            ]);
    }
    
}
