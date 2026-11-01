<?php

namespace App\Mail;

use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookReturnedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $loan;

    public function __construct(Loan $loan)
    {
        $this->loan = $loan->load(['user', 'localCopy.centralItem', 'localCopy.library']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Devolução confirmada com sucesso!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.book_returned',
        );
    }
}
