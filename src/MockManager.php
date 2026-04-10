<?php

namespace MohammadZarifiyan\Telegram;

use Closure;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use MohammadZarifiyan\Telegram\Enums\RestrictionType;
use MohammadZarifiyan\Telegram\Interfaces\MockManager as MockManagerInterface;
use Throwable;

class MockManager implements MockManagerInterface
{
    protected bool $isRecording = false;
    protected array $promises = [];
    protected array $pairs = [];

    public function isRecording(): bool
    {
        return $this->isRecording;
    }

    public function startRecording(): static
    {
        $this->isRecording = true;
        $this->promises = [];
        $this->pairs = [];

        return $this;
    }

    public function addPromise(Promise $promise): static
    {
        $this->promises[] = $promise;

        return $this;
    }

    public function promisedHttpResponse(string $apiKey, string $endpoint, string $method): ?Response
	{
		$promises = new Collection($this->promises);
		$normalizedEndpoint = Str::of($endpoint)
            ->lower()
            ->remove(['http://', 'https://']);

        /**
         * @var ?Promise $promise
         */
        $promise = $promises->firstWhere(function (Promise $promise) use ($apiKey, $endpoint, $method, $normalizedEndpoint) {
            if ($promise->apiKeyRestrictionType === RestrictionType::Legalization && !in_array($apiKey, $promise->apiKeys)) {
                return false;
            }

            if ($promise->apiKeyRestrictionType === RestrictionType::Prohibition && in_array($apiKey, $promise->apiKeys)) {
                return false;
            }

            $normalizedPromiseEndpoints = array_map(
                fn (string $endpoint) => Str::of($endpoint)->lower()->remove(['http://', 'https://']),
                $promise->endpoints
            );

            if ($promise->endpointsRestrictionType === RestrictionType::Legalization && !in_array($normalizedEndpoint, $normalizedPromiseEndpoints)) {
                return false;
            }

            if ($promise->endpointsRestrictionType === RestrictionType::Prohibition && in_array($normalizedEndpoint, $normalizedPromiseEndpoints)) {
                return false;
            }

            return is_null($promise->method) || strtolower($promise->method) === strtolower($method);
        });

		if ($promise instanceof Promise) {
			$psr7Response = Factory::psr7Response($promise->body, $promise->statusCode, $promise->headers);

			return new Response($psr7Response);
		}

		if ($promises->isNotEmpty()) {
			return null;
		}

		$psr7Response = Factory::psr7Response();

		return new Response($psr7Response);
    }

    public function pair(PendingTelegramRequest $pendingTelegramRequest, Response|Throwable $response): static
    {
        $this->pairs[] = compact('pendingTelegramRequest', 'response');

        return $this;
    }

    public function recorded(?Closure $callback = null): Collection
    {
        $collect = new Collection($this->pairs);

        if ($callback) {
            return $collect->filter(fn (array $pair) => $callback($pair['pendingTelegramRequest'], $pair['response']));
        }

        return $collect;
    }
}