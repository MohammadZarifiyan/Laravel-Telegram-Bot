<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

interface SecureTokenRepository
{
    public function get(): ?string;
}
