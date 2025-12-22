<?php

namespace App\Mail;

use App\Models\SellerApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SellerApplicationStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public SellerApplication $application;
    public string $status;
    public ?string $reason;
    public ?array $credentials;

    public function __construct(SellerApplication $application, string $status, ?string $reason = null, ?array $credentials = null)
    {
        $this->application = $application;
        $this->status = $status;
        $this->reason = $reason;
        $this->credentials = $credentials;

        $subject = $status === 'approved'
            ? 'Your seller application has been approved'
            : 'Your seller application status';

        $this->subject($subject);
    }

    public function build()
    {
        return $this->view('emails.seller_application_status')
            ->with([
                'application' => $this->application,
                'status' => $this->status,
                'reason' => $this->reason,
            ]);
    }
}
