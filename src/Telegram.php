<?php

namespace MohammadZarifiyan\Telegram;

use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use MohammadZarifiyan\Telegram\Exceptions\TelegramException;
use MohammadZarifiyan\Telegram\Exceptions\TelegramOriginException;
use MohammadZarifiyan\Telegram\Interfaces\Payload;
use MohammadZarifiyan\Telegram\Interfaces\PendingRequestStack;
use MohammadZarifiyan\Telegram\Interfaces\Telegram as TelegramInterface;

class Telegram implements TelegramInterface
{
	protected ?Update $update;
	
	public function __construct(protected string $apiKey, protected Container $container)
	{
		//
	}

	public function fresh(string $apiKey, Container $container = null): static
	{
		return new static($apiKey, $container ?? $this->container);
	}

	/**
	 * @throws TelegramException
	 * @throws TelegramOriginException
	 */
	public function handleRequest(Request $request): void
	{
		if (!($request instanceof Update)) {
			$this->update = Update::createFrom($request)->setContainer($this->container);
		}

		$update_handler = new UpdateHandler($this->update);

		foreach ($update_handler->run() as $update) {
			$this->update = $update;
		}
	}

	public function getUpdate(): ?Update
	{
		return @$this->update;
	}

	public function execute(Payload|string $payload, array $merge = []): Response
    {
		$payload = try_resolve($payload);

		$executor = new Executor;

		return $executor->run(
			new PendingRequest($payload, $merge, $this->apiKey)
		);
    }

	public function async(Closure $closure): array
    {
		$stack = App::make(PendingRequestStack::class);

		$closure($stack);

		$executor = new Executor;

		return $executor->runAsync(
			$stack->toArray()
		);
    }
	
	public function generateFileUrl(string $filePath): string
	{
		return sprintf('https://api.telegram.org/file/bot%s/%s', $this->apiKey, $filePath);
	}
}
