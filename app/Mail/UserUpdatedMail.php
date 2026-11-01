<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user->load('libraries');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Suas informações no Biblió foram atualizadas!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user_updated',
        );
    }
}
