<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

use Closure;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use MohammadZarifiyan\Telegram\PendingTelegramRequest;
use MohammadZarifiyan\Telegram\Promise;

interface MockManager
{
    public function isRecording(): bool;

    public function startRecording(): static;

    public function addPromise(Promise $promise): static;

    public function promisedHttpResponse(string $apiKey, string $endpoint, string $method): Response;

    public function pair(PendingTelegramRequest $pendingTelegramRequest, Response $response): static;

    public function recorded(?Closure $callback = null): Collection;
}
