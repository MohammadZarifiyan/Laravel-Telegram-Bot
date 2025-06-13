<?php

namespace MohammadZarifiyan\Telegram;

use MohammadZarifiyan\Telegram\Interfaces\PendingRequestStack as PendingRequestStackInterface;
use MohammadZarifiyan\Telegram\Interfaces\PendingRequestBuilder as PendingRequestBuilderInterface;

class PendingRequestStack implements PendingRequestStackInterface
{
    protected array $pendingRequestBuilders;

    public function __construct(protected ?string $endpoint = null, protected ?string $apiKey = null)
    {
        //
    }

    public function add(?string $as = null): PendingRequestBuilderInterface
    {
        $pendingRequestBuilder = new PendingRequestBuilder($this->endpoint, $this->apiKey);

        if (is_string($as)) {
            return $this->pendingRequestBuilders[$as] = $pendingRequestBuilder;
        }

        return $this->pendingRequestBuilders[] = $pendingRequestBuilder;
    }

    public function toArray(): array
    {
        $keys = array_keys($this->pendingRequestBuilders);
        $values = array_map(
            fn (PendingRequestBuilderInterface $pendingRequestBuilder) => $pendingRequestBuilder->toPendingRequest(),
            $this->pendingRequestBuilders
        );

        return array_combine($keys, $values);
    }
}
