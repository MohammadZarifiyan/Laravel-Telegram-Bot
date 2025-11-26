<?php

namespace MohammadZarifiyan\Telegram;

class TelegramBotApiKey
{
    public function __construct(public int $botId, public string $botTokenHash)
    {
        //
    }

    public function __toString(): string
    {
        return $this->botId . ':' . $this->botTokenHash;
    }
}
