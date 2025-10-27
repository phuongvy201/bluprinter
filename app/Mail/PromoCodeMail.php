<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PromoCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->subject('[Promo Code Request] ' . ($data['email'] ?? '')); 
    }

    public function build()
    {
        return $this->view('emails.promo_code')
            ->with(['data' => $this->data]);
    }
}
