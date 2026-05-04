<?php

namespace MohammadZarifiyan\Telegram;

use Illuminate\Support\Traits\Conditionable;
use MohammadZarifiyan\Telegram\Interfaces\ReplyMarkup;

class PendingTelegramRequestBuilder
{
    use Conditionable;

    protected string $method;
    protected array $data = [];
    protected ReplyMarkup|string|null $replyMarkup = null;
    protected ?string $apiKey = null;
    protected ?string $endpoint = null;

    public function __construct(
        protected ?string $initialEndpoint = null,
        protected ?string $initialApiKey = null
    ) {
        //
    }

    public function setMethod(string $method): static
    {
        $this->method = $method;

        return $this;
    }

    public function setData(array $data = []): static
    {
        $this->data = $data;

        return $this;
    }

    public function setReplyMarkup(ReplyMarkup|string|null $replyMarkup = null): static
    {
        $this->replyMarkup = $replyMarkup;

        return $this;
    }

    public function setApiKey(?string $apikey = null): static
    {
        $this->apiKey = $apikey;

        return $this;
    }

    public function setEndpoint(?string $endpoint = null): static
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function toPendingTelegramRequest(): PendingTelegramRequest
    {
        return new PendingTelegramRequest(
			$this->apiKey ?? $this->initialApiKey,
            $this->endpoint ?? $this->initialEndpoint,
            $this->method,
            $this->data,
            $this->replyMarkup
        );
    }
}