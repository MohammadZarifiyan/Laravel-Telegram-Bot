<?php

namespace MohammadZarifiyan\Telegram;

use MohammadZarifiyan\Telegram\Interfaces\PendingRequestStack as PendingRequestInterface;
use MohammadZarifiyan\Telegram\Interfaces\ReplyMarkup;

class PendingRequestStack implements PendingRequestInterface
{
    protected array $pendingRequests;

    public function __construct(protected string $endpoint, protected string $apiKey)
    {
        //
    }

    public function add(string $method, array $data = [], ReplyMarkup|string|null $replyMarkup = null, string $apiKey = null, string $endpoint = null): static
    {
        $this->pendingRequests[] = new PendingRequest(
            $endpoint ?? $this->endpoint,
            $apiKey ?? $this->apiKey,
            $method,
            $data,
            $replyMarkup
        );

        return $this;
    }

    public function toArray(): array
    {
        return $this->pendingRequests;
    }
}
