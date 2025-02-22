<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

use Illuminate\Support\Collection;

interface ProxyRepository
{
    public function get(): Collection;
}
