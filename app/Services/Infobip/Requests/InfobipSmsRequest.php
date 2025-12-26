<?php

namespace App\Services\Infobip\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Http\Auth\HeaderAuthenticator;

class InfobipSmsRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private string $phone,
        private string $message
    ) {}

    public function resolveEndpoint(): string
    {
        return config('infobip.sms_endpoint');
    }

    protected function defaultAuth(): HeaderAuthenticator
    {
        return new HeaderAuthenticator('App ' . config('infobip.api_key'));
    }

    protected function defaultBody(): array
    {
        return [
            'messages' => [
                [
                    'from' => 'TABsense',
                    'destinations' => [
                        [
                            'to' => $this->phone,
                        ],
                    ],
                    'text' => $this->message,
                ],
            ],
        ];
    }
}
