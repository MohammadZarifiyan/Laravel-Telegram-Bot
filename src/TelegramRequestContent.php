<?php

namespace MohammadZarifiyan\Telegram;

use MohammadZarifiyan\Telegram\Interfaces\ReplyMarkup;
use MohammadZarifiyan\Telegram\Interfaces\TelegramRequestContent as TelegramRequestContentInterface;

class TelegramRequestContent implements TelegramRequestContentInterface
{
    public static function fresh(): static
    {
        return new static;
    }

    public function setMethod(string $method): static
    {
        $this->method = $method;

        return $this;
    }

    public function setData(array $data = []): static
    {
        $this->data = $data;

        return $this;
    }

    public function setReplyMarkup(?ReplyMarkup $replyMarkup): static
    {
        $this->replyMarkup = $replyMarkup;

        return $this;
    }
}
