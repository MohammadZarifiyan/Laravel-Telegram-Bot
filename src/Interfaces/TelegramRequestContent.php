<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

interface TelegramRequestContent
{
    public function __construct(?string $method = null, array $data = [], ?ReplyMarkup $replyMarkup = null);

    public static function fresh(string $method = null, array $data = [], ?ReplyMarkup $replyMarkup = null): static;

    public function setMethod(string $method): static;

    public function setData(array $data = []): static;

    public function setReplyMarkup(?ReplyMarkup $replyMarkup): static;
}
