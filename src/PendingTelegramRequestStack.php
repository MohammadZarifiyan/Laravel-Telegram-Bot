<?php

namespace MohammadZarifiyan\Telegram;

class PendingTelegramRequestStack
{
    protected array $pendingTelegramRequestBuilders;

    public function __construct(
        protected readonly ?string $endpoint = null,
        protected readonly ?string $apiKey = null
    ) {
        //
    }

    public function add(?string $as = null): PendingTelegramRequestBuilder
    {
        $pendingTelegramRequestBuilder = new PendingTelegramRequestBuilder($this->endpoint, $this->apiKey);

        if (is_string($as)) {
            return $this->pendingTelegramRequestBuilders[$as] = $pendingTelegramRequestBuilder;
        }

        return $this->pendingTelegramRequestBuilders[] = $pendingTelegramRequestBuilder;
    }

    public function toArray(): array
    {
        $keys = array_keys($this->pendingTelegramRequestBuilders);
        $values = array_map(
            fn (PendingTelegramRequestBuilder $pendingTelegramRequestBuilder) => $pendingTelegramRequestBuilder->toPendingTelegramRequest(),
            $this->pendingTelegramRequestBuilders
        );

        return array_combine($keys, $values);
    }
}
