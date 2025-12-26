<?php

namespace App\Services\Infobip;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class InfobipConnector extends Connector
{
    use AcceptsJson;

    public function resolveBaseUrl(): string
    {
        return config('infobip.base_url');
    }

    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }
}
