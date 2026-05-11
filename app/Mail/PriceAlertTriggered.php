<?php

namespace App\Mail;

use App\Models\PriceAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PriceAlertTriggered extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PriceAlert $alert,
        public int $currentPrice
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🔔 Alerte prix : {$this->alert->item->name} sur " . ucfirst($this->alert->server),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.price-alert',
        );
    }
}
