<?php

namespace MohammadZarifiyan\Telegram;

use Illuminate\Support\Arr;
use MohammadZarifiyan\Telegram\Enums\RestrictionType;

class Promise
{
    public array $headers = [];
    public int $statusCode = 200;
    public null|array|string $body = null;
    public ?RestrictionType $apiKeyRestrictionType = null;
    public array $apiKeys = [];
    public ?RestrictionType $endpointsRestrictionType = null;
    public array $endpoints = [];

    public function __construct(public ?string $method = null)
    {
        //
    }

    public static function on(?string $method = null): static
    {
        return new static($method);
    }

    public function setHeaders(array $headers): static
    {
        $this->headers = $headers;

        return $this;
    }

    public function setStatusCode(int $statusCode): static
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function setBody(null|array|string $body): static
    {
        $this->body = $body;

        return $this;
    }

    public function onlyApiKeys(array|string $apiKeys = []): static
    {
        $this->apiKeys = Arr::wrap($apiKeys);
        $this->apiKeyRestrictionType = RestrictionType::Legalization;

        return $this;
    }

    public function exceptApiKeys(array|string $apiKeys = []): static
    {
        $this->apiKeys = Arr::wrap($apiKeys);
        $this->apiKeyRestrictionType = RestrictionType::Prohibition;

        return $this;
    }

    public function onlyEndpoints(array|string $endpoints = []): static
    {
        $this->endpoints = Arr::wrap($endpoints);
        $this->endpointsRestrictionType = RestrictionType::Legalization;

        return $this;
    }

    public function exceptEndpoints(array|string $endpoints = []): static
    {
        $this->endpoints = Arr::wrap($endpoints);
        $this->endpointsRestrictionType = RestrictionType::Prohibition;

        return $this;
    }
}
