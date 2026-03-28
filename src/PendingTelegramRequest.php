<?php

namespace MohammadZarifiyan\Telegram;

use MohammadZarifiyan\Telegram\Interfaces\ReplyMarkup;

class PendingTelegramRequest
{
    public function __construct(
        public readonly string $apiKey,
        public readonly string $endpoint,
        public readonly string $method,
        public readonly array $data = [],
        public readonly ReplyMarkup|string|null $replyMarkup = null
    ) {
        //
    }
}
