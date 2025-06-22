<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

interface TelegramRequestContent
{
    public static function fresh(): static;

    public function setMethod(string $method): static;

    public function setData(array $data = []): static;

    public function setReplyMarkup(?ReplyMarkup $replyMarkup): static;
}
