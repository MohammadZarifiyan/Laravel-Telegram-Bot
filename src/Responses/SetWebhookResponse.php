<?php

namespace MohammadZarifiyan\Telegram\Responses;

use MohammadZarifiyan\Telegram\Interfaces\Response;

class SetWebhookResponse implements Response
{
    /**
     * @inheritDoc
     */
    public function method(): string
    {
        return 'setWebhook';
    }

    /**
     * @inheritDoc
     */
    public function data(): array
    {
        return [
            'url' => route(config('telegram.update-route'))
        ];
    }
}
