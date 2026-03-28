<?php

namespace MohammadZarifiyan\Telegram\Events;

use Illuminate\Foundation\Events\Dispatchable;
use MohammadZarifiyan\Telegram\Proxy;

class ProxyUsed
{
    use Dispatchable;

    public function __construct(public Proxy $proxy)
    {
        //
    }
}