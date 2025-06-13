<?php

namespace MohammadZarifiyan\Telegram;

use MohammadZarifiyan\Telegram\Interfaces\PendingRequest as PendingRequestInterface;
use MohammadZarifiyan\Telegram\Interfaces\ReplyMarkup;
use MohammadZarifiyan\Telegram\Interfaces\PendingRequestBuilder as PendingRequestBuilderInterface;

class PendingRequestBuilder implements PendingRequestBuilderInterface
{
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

    public function toPendingRequest(): PendingRequestInterface
    {
        return new PendingRequest(
            $this->endpoint ?? $this->initialEndpoint,
            $this->apiKey ?? $this->initialApiKey,
            $this->method,
            $this->data,
            $this->replyMarkup
        );
    }
}