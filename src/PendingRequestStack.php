<?php

namespace MohammadZarifiyan\Telegram;

class PendingRequestStack
{
    protected array $pendingRequestBuilders;

    public function __construct(protected ?string $endpoint = null, protected ?string $apiKey = null)
    {
        //
    }

    public function add(?string $as = null): PendingRequestBuilder
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
            fn (PendingRequestBuilder $pendingRequestBuilder) => $pendingRequestBuilder->toPendingRequest(),
            $this->pendingRequestBuilders
        );

        return array_combine($keys, $values);
    }
}
