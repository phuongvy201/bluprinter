<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderTrackingNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $trackingNumber;
    public $status;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, $trackingNumber = null, $status = null)
    {
        $this->order = $order;
        $this->trackingNumber = $trackingNumber;
        $this->status = $status;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = 'Order Update - ' . $this->order->order_number;

        if ($this->trackingNumber) {
            $subject = 'Your Order Has Been Shipped - ' . $this->order->order_number;
        } elseif ($this->status) {
            $subject = 'Order Status Update - ' . $this->order->order_number;
        }

        return new Envelope(
            subject: $subject,
            from: config('mail.from.address'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order-tracking-notification',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
