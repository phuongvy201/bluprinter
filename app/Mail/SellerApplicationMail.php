<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SellerApplicationMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->subject('[Seller Application] ' . ($data['store_name'] ?? $data['name']));
    }

    public function build()
    {
        return $this->view('emails.seller_application')
            ->with(['data' => $this->data]);
    }
}

