<?php

namespace App\Mail;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpiringMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Subscription $subscription
    ) {}

    public function envelope(): Envelope
    {
        $days = $this->subscription->daysUntilExpiry();

        return new Envelope(
            subject: "تنبيه: اشتراكك في TABsense سينتهي خلال {$days} يوم",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.subscription-expiring',
        );
    }
}
