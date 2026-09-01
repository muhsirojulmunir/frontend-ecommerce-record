<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountDeletionCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $code, public string $userName)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kode Verifikasi Penghapusan Akun - RECORD Official Store',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-deletion-code',
            with: [
                'code' => $this->code,
                'userName' => $this->userName,
            ],
        );
    }
}