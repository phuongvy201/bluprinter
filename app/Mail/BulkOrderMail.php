<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BulkOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;
    public $designFile;

    public function __construct(array $data, $designFile = null)
    {
        $this->data = $data;
        $this->designFile = $designFile;
        $this->subject('[Bulk Order] Quote Request');
    }

    public function build()
    {
        $email = $this->view('emails.bulk_order')
            ->with(['data' => $this->data]);

        if ($this->designFile) {
            $email->attach($this->designFile->getRealPath(), [
                'as' => $this->designFile->getClientOriginalName(),
                'mime' => $this->designFile->getMimeType(),
            ]);
        }

        return $email;
    }
}
