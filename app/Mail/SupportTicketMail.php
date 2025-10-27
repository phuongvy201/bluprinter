<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Storage;

class SupportTicketMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;
    public $attachmentFile;

    /**
     * Create a new message instance.
     */
    public function __construct(array $data, $attachmentFile = null)
    {
        $this->data = $data;
        $this->attachmentFile = $attachmentFile;
        $this->subject('[Support Ticket] ' . ($data['subject'] ?? 'New Ticket'));
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $email = $this->view('emails.support_ticket')
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
