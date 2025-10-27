<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupportRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;
    public $attachmentFile;

    public function __construct(array $data, $attachmentFile = null)
    {
        $this->data = $data;
        $this->attachmentFile = $attachmentFile;
        $this->subject('[Support Request] ' . ($data['subject'] ?? 'New Request'));
    }

    public function build()
    {
        $email = $this->view('emails.support_request')
            ->with(['data' => $this->data]);

        if ($this->attachmentFile) {
            $email->attach($this->attachmentFile->getRealPath(), [
                'as' => $this->attachmentFile->getClientOriginalName(),
                'mime' => $this->attachmentFile->getMimeType(),
            ]);
        }

        return $email;
    }
}
