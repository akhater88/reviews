<?php

namespace App\Mail\Competition;

use App\Models\Competition\CompetitionParticipant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WinnerNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CompetitionParticipant $participant,
        public array $data
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->data['winner_type'] === 'lottery'
            ? 'مبروك! فزت في سحب مسابقة أفضل مطعم'
            : 'مبروك! المطعم الذي رشحته فاز في المسابقة';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.competition.winner-notification',
            with: [
                'participant' => $this->participant,
                'data' => $this->data,
            ]
        );
    }
}
