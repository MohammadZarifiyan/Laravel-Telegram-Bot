<?php

namespace MohammadZarifiyan\Telegram\Events;

use Illuminate\Foundation\Events\Dispatchable;
use MohammadZarifiyan\Telegram\Interfaces\Proxy;

class ProxyUsed
{
    use Dispatchable;

    public function __construct(public Proxy $proxy)
    {
        //
    }
}