<?php

namespace MohammadZarifiyan\Telegram\Repositories;

use MohammadZarifiyan\Telegram\Interfaces\SecureTokenRepository as SecureTokenRepositoryInterface;

class SecureTokenRepository implements SecureTokenRepositoryInterface
{
    public function get(): ?string
    {
        return config('services.telegram.secure-token');
    }
}
