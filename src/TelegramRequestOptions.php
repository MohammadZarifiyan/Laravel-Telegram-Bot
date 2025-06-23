<?php

namespace MohammadZarifiyan\Telegram;

class TelegramRequestOptions
{
    public int|string $recipient;
    public ?string $apiKey = null;
    public ?string $endpoint = null;

    public static function fresh(): static
    {
        return new static;
    }

    public function setRecipient(int|string $recipient): static
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function setApiKey(?string $apiKey = null): static
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function setEndpoint(?string $endpoint = null): static
    {
        $this->endpoint = $endpoint;

        return $this;
    }
}
