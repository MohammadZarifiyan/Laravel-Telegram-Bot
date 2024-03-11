<?php

namespace MohammadZarifiyan\Telegram\Repositories;

use MohammadZarifiyan\Telegram\Interfaces\EndpointRepository as EndpointRepositoryInterface;

class EndpointRepository implements EndpointRepositoryInterface
{
    public function get(): ?string
    {
        return env('TELEGRAM_ENDPOINT', 'https://api.telegram.org');
    }
}
