<?php

namespace MohammadZarifiyan\Telegram\Events;

use Illuminate\Foundation\Events\Dispatchable;
use MohammadZarifiyan\Telegram\Proxy;

class ProxyFailed
{
    use Dispatchable;

    public function __construct(public Proxy $proxy)
    {
        //
    }
}