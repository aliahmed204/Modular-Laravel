<?php

namespace Modules\Order\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $localizedOrderTotal
    ) {}

    public function envelope()
    {
        return new Envelope(
            subject: 'Order Received',
        );
    }

    public function content()
    {
        return new Content(
            view: null,
            html: 'We have received your order of '.$this->localizedOrderTotal
        );
    }
}
