<?php

namespace MohammadZarifiyan\Telegram\Repositories;

use MohammadZarifiyan\Telegram\Interfaces\ApiKeyRepository as ApiKeyRepositoryInterface;

class ApiKeyRepository implements ApiKeyRepositoryInterface
{
    public function get(): ?string
    {
        return env('TELEGRAM_API_KEY');
    }
}
