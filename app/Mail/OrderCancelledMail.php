<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order->loadMissing(['items.product', 'items.productVariant', 'user']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Pesanan #{$this->order->order_number} Telah Dibatalkan - RECORD Official Store",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-cancelled',
            with: [
                'order' => $this->order,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}