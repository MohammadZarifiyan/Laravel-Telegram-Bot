<?php

namespace MohammadZarifiyan\Telegram;

use Closure;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Traits\Macroable;
use MohammadZarifiyan\Telegram\Exceptions\TelegramException;
use MohammadZarifiyan\Telegram\Exceptions\TelegramOriginException;
use MohammadZarifiyan\Telegram\Interfaces\Payload;
use MohammadZarifiyan\Telegram\Interfaces\PendingRequestStack;
use MohammadZarifiyan\Telegram\Interfaces\PendingRequest;

class Service
{
	use Macroable;
	
	protected ?Update $update;
	
	public function __construct(protected string $apiKey, protected string $endpoint)
	{
		//
	}

	public function fresh(string $apiKey, string $endpoint = null): static
	{
		return new static($apiKey, $endpoint ?? config('telegram.endpoint'));
	}
	
	public function setApiKey(string $apiKey): static
	{
		$this->apiKey = $apiKey;
		
		return $this;
	}

	/**
	 * @throws TelegramException
	 * @throws TelegramOriginException|\ReflectionException
	 */
	public function handleRequest(Request $request): void
	{
		if (!($request instanceof Update)) {
			$this->update = Update::createFrom($request);
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
		$executor = new Executor;

		$pending_request = App::makeWith(PendingRequest::class, [
			'endpoint' => $this->endpoint,
			'apiKey' => $this->apiKey,
			'payload' => try_resolve($payload),
			'merge' => $merge
		]);
		
		return $executor->run($pending_request);
    }

	public function executeAsync(Closure $closure): array
    {
		/**
		 * @var PendingRequestStack $stack
		 */
		$stack = App::makeWith(PendingRequestStack::class, ['endpoint' => $this->endpoint, 'apiKey' => $this->apiKey]);

		$closure($stack);

		$executor = new Executor;

		return $executor->runAsync(
			$stack->toArray()
		);
    }
	
	public function generateFileUrl(string $filePath): string
	{
		return sprintf('%s/file/bot%s/%s', $this->endpoint, $this->apiKey, $filePath);
	}
}
