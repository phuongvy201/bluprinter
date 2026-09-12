<?php

namespace App\Mail;

use App\Models\PromoCode;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PromoCodeIssuedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PromoCode $promoCode,
        public string $recipientName = '',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your exclusive promo code — ' . $this->promoCode->code,
            from: config('mail.from.address'),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.promo-code-issued');
    }
}
