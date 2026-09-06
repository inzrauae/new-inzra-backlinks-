<?php

namespace App\Mail;

use App\Models\SeoOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SeoOrderReceived extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public SeoOrder $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Payment received — SEO order {$this->order->order_number} | INZRA",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.seo-order-received',
        );
    }
}
