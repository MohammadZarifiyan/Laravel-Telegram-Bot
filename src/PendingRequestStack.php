<?php

namespace MohammadZarifiyan\Telegram;

use MohammadZarifiyan\Telegram\Interfaces\PendingRequest as PendingRequestInterface;
use MohammadZarifiyan\Telegram\Interfaces\PendingRequestStack as PendingRequestStackInterface;
use MohammadZarifiyan\Telegram\Interfaces\PendingRequestAdder as PendingRequestAdderInterface;

class PendingRequestStack implements PendingRequestStackInterface
{
    protected array $pendingRequests;

    public function __construct(protected string $endpoint, protected string $apiKey)
    {
        //
    }

    public function add(string $method): PendingRequestAdderInterface
    {
        return new PendingRequestAdder(
            function (PendingRequestInterface $pendingRequest) {
                $this->pendingRequests[] = $pendingRequest;
            },
            $method
        );
    }

    public function toArray(): array
    {
        return $this->pendingRequests;
    }
}
