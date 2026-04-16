<?php

namespace MohammadZarifiyan\Telegram\Events;

use Illuminate\Foundation\Events\Dispatchable;
use MohammadZarifiyan\Telegram\PendingHttpRequest;
use MohammadZarifiyan\Telegram\PendingTelegramRequest;

class RequestSending
{
    use Dispatchable;

    public function __construct(public PendingTelegramRequest $pendingTelegramRequest, public PendingHttpRequest $pendingHttpRequest)
    {
        //
    }
}