<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

use MohammadZarifiyan\Telegram\Interfaces\PendingRequest as PendingRequestInterface;

interface PendingRequestBuilder
{
    public function when($value = null, callable $callback = null, callable $default = null);

    public function unless($value = null, callable $callback = null, callable $default = null);

    public function setMethod(string $method): static;

    public function setData(array $data = []): static;

    public function setReplyMarkup(ReplyMarkup|string|null $replyMarkup = null): static;

    public function setApiKey(?string $apikey = null): static;

    public function setEndpoint(?string $endpoint = null): static;

    public function toPendingRequest(): PendingRequestInterface;
}
