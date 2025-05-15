<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

interface SecretTokenRepository
{
    public function get(): ?string;
}
