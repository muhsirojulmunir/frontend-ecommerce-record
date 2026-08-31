<?php

namespace App\Mail;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class OrderInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public string $orderUrl;

    public function __construct(Order $order)
    {
        $this->order = $order->loadMissing(['items.product', 'items.productVariant', 'user']);
        $this->orderUrl = url('/orders/' . $order->order_number);
    }

    public function envelope(): Envelope
    {
        $statusText = ($this->order->payment_status === 'paid') ? 'LUNAS' : 'Menunggu Pembayaran';
        $nomor = $this->order->invoice_number ?: $this->order->order_number;
        return new Envelope(
            subject: "Invoice Pesanan #{$nomor} [{$statusText}] - RECORD Official Store",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-invoice',
            with: [
                'order' => $this->order,
                'orderUrl' => $this->orderUrl,
            ],
        );
    }

    public function attachments(): array
    {
        try {
            $pdf = Pdf::loadView('pdf.invoice', ['order' => $this->order])
                      ->setPaper('a4', 'portrait');

            $filename = 'Invoice-' . ($this->order->invoice_number ?: $this->order->order_number) . '.pdf';

            return [
                Attachment::fromData(fn () => $pdf->output(), $filename)
                    ->withMime('application/pdf'),
            ];
        } catch (\Throwable $e) {
            Log::error('Gagal melampirkan PDF Invoice ke email: ' . $e->getMessage());
            return [];
        }
    }
}
