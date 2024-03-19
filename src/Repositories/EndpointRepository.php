<?php

namespace MohammadZarifiyan\Telegram\Repositories;

use MohammadZarifiyan\Telegram\Interfaces\EndpointRepository as EndpointRepositoryInterface;

class EndpointRepository implements EndpointRepositoryInterface
{
    public function get(): ?string
    {
        return config('services.telegram.endpoint', 'https://api.telegram.org');
    }
}
