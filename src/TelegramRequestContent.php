<?php

namespace MohammadZarifiyan\Telegram;

use MohammadZarifiyan\Telegram\Interfaces\ReplyMarkup;

class TelegramRequestContent
{
    public string $method;
    public array $data = [];
    public ?ReplyMarkup $replyMarkup = null;

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

    public function setReplyMarkup(?ReplyMarkup $replyMarkup = null): static
    {
        $this->replyMarkup = $replyMarkup;

        return $this;
    }
}
