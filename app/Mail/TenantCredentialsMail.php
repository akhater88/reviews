<?php

namespace App\Mail;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public ?string $password,
        public Tenant $tenant,
        public string $loginUrl,
        public bool $isReset = false
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->isReset
                ? 'TABsense - تم إعادة تعيين كلمة المرور'
                : 'TABsense - بيانات الدخول الخاصة بك',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tenant-credentials',
        );
    }
}
