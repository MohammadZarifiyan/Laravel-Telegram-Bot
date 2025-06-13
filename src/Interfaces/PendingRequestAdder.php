<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

interface PendingRequestAdder
{
    public function setData(array $data = []): static;

    public function setReplyMarkup(ReplyMarkup|string|null $replyMarkup = null): static;

    public function setApiKey(?string $apikey = null): static;

    public function setEndpoint(?string $endpoint = null): static;

    public function as(?string $as = null): static;
}
