<?php

namespace App\Services\Infobip\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Http\Auth\HeaderAuthenticator;

class InfobipWhatsAppRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private string $phone,
        private string $otpCode
    ) {}

    public function resolveEndpoint(): string
    {
        return '/whatsapp/1/message/template';
    }

    protected function defaultAuth(): HeaderAuthenticator
    {
        return new HeaderAuthenticator('App ' . config('infobip.whatsapp.api_key'));
    }

    protected function defaultBody(): array
    {
        return [
            'messages' => [
                [
                    'from' => config('infobip.whatsapp.sender_number'),
                    'to' => $this->phone,
                    'content' => [
                        'templateName' => config('infobip.whatsapp.template_name'),
                        'language' => config('infobip.whatsapp.template_lang'),
                        'templateData' => [
                            'body' => ['placeholders' => [$this->otpCode]],
                            'buttons' => [
                                [
                                    'type' => 'URL',
                                    'parameter' => $this->otpCode,
                                ],
                            ],
                        ],
                    ],
                    'callbackData' => 'otp-verification',
                ],
            ],
        ];
    }
}
