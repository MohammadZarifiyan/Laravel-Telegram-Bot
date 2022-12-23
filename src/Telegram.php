<?php

namespace MohammadZarifiyan\Telegram;

use Closure;
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
	
	public function __construct(protected string $apiKey)
	{
		//
	}

	public function fresh(string $apiKey): static
	{
		return new static($apiKey);
	}

	/**
	 * @throws TelegramException
	 * @throws TelegramOriginException
	 */
	public function handleRequest(Request $request): void
	{
		if (!($request instanceof Update)) {
			$this->update = Update::createFrom($request);
		}

		$update_handler = new UpdateHandler($this->update);

		$update_generator = $update_handler->run();

		foreach ($update_generator as $update) {
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
