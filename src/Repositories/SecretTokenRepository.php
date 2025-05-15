<?php

namespace MohammadZarifiyan\Telegram\Repositories;

use MohammadZarifiyan\Telegram\Interfaces\SecretTokenRepository as SecretTokenRepositoryInterface;

class SecretTokenRepository implements SecretTokenRepositoryInterface
{
    public function get(): ?string
    {
        return config('services.telegram.secret-token');
    }
}
