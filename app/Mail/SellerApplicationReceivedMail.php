<?php

namespace App\Mail;

use App\Models\SellerApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SellerApplicationReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public SellerApplication $application;

    public function __construct(SellerApplication $application)
    {
        $this->application = $application;
        $this->subject('We received your seller application');
    }

    public function build()
    {
        return $this->view('emails.seller_application_received')
            ->with(['application' => $this->application]);
    }
}

