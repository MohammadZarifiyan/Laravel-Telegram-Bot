<?php

namespace MohammadZarifiyan\Telegram;

use Closure;
use MohammadZarifiyan\Telegram\Interfaces\ReplyMarkup;
use MohammadZarifiyan\Telegram\Interfaces\PendingRequestAdder as PendingRequestAdderInterface;

class PendingRequestAdder implements PendingRequestAdderInterface
{
    public array $data = [];
    public ReplyMarkup|string|null $replyMarkup = null;
    public ?string $apiKey = null;
    public ?string $endpoint = null;
    public ?string $as = null;

    public function __construct(private Closure $closure, public string $method)
    {
        //
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

    public function as(?string $as = null): static
    {
        $this->as = $as;

        return $this;
    }

    public function __destruct()
    {
        $pendingRequest = new PendingRequest(
            $this->endpoint,
            $this->apiKey,
            $this->method,
            $this->data,
            $this->replyMarkup
        );

        call_user_func($this->closure, $pendingRequest);
    }
}