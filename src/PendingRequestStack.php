<?php

namespace MohammadZarifiyan\Telegram;

use MohammadZarifiyan\Telegram\Interfaces\Payload;
use MohammadZarifiyan\Telegram\Interfaces\PendingRequestStack as PendingRequestInterface;
use MohammadZarifiyan\Telegram\Interfaces\ReplyMarkup;

class PendingRequestStack implements PendingRequestInterface
{
    protected array $pendingRequests;

    public function __construct(protected string $endpoint, protected string $apiKey)
    {
        //
    }

    public function addPayload(Payload|string $payload, array $merge = [], string $apiKey = null, string $endpoint = null): static
    {
        $this->pendingRequests[] = new PayloadPendingRequest(
            $endpoint ?? $this->endpoint,
            $apiKey ?? $this->apiKey,
            try_resolve($payload),
            $merge
        );

        return $this;
    }

    public function addRaw(string $method, array $data = [], ReplyMarkup|string|null $replyMarkup = null, string $apiKey = null, string $endpoint = null): static
    {
        $this->pendingRequests[] = new RawPendingRequest(
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
