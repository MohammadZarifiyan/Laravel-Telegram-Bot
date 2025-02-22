<?php

namespace MohammadZarifiyan\Telegram\Repositories;

use Illuminate\Support\Collection;
use MohammadZarifiyan\Telegram\Interfaces\Proxy;
use MohammadZarifiyan\Telegram\Interfaces\ProxyRepository as ProxyInterface;

class ProxyRepository implements ProxyInterface
{
    public function get(): Collection
    {
        $list = config('services.telegram.proxies', []);
        $list = new Collection($list);

        return $list->map([$this, 'mapToProxy']);
    }

    public function mapToProxy(string $proxy): Proxy
    {
        return new class ($proxy) implements Proxy {
            public function __construct(public string $proxy)
            {
                //
            }

            public function getKey(): string
            {
                return $this->proxy;
            }

            public function getConfiguration(): string
            {
                return $this->proxy;
            }
        };
    }
}
