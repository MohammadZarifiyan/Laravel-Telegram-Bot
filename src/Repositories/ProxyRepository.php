<?php

namespace MohammadZarifiyan\Telegram\Repositories;

use Illuminate\Support\Collection;
use MohammadZarifiyan\Telegram\Interfaces\ProxyRepository as ProxyInterface;
use MohammadZarifiyan\Telegram\Proxy;

class ProxyRepository implements ProxyInterface
{
    public function get(): Collection
    {
        $list = config('services.telegram.proxies', []);

        return collect($list)->map(fn ($item) => new Proxy($item));
    }
}
