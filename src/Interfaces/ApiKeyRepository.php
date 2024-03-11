<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

interface ApiKeyRepository
{
    public function get(): ?string;
}
