<?php

namespace MohammadZarifiyan\Telegram;

use Closure;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Traits\Macroable;
use MohammadZarifiyan\Telegram\Interfaces\Telegram as TelegramInterface;
use MohammadZarifiyan\Telegram\Interfaces\ReplyMarkup;
use MohammadZarifiyan\Telegram\Exceptions\TelegramException;
use MohammadZarifiyan\Telegram\Exceptions\TelegramOriginException;
use MohammadZarifiyan\Telegram\Interfaces\Payload;
use MohammadZarifiyan\Telegram\Interfaces\PendingRequestStack;

class Telegram implements TelegramInterface
{
	use Macroable;
	
	protected ?Update $update;
	
	public function __construct(protected string $apiKey, protected string $endpoint)
	{
		//
	}
	
	public function setApiKey(string $apiKey): static
	{
		$this->apiKey = $apiKey;
		
		return $this;
	}

	public function setEndpoint(string $endpoint): static
	{
		$this->endpoint = $endpoint;

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
		$pending_request = new PayloadPendingRequest(
            $this->endpoint,
            $this->apiKey,
            try_resolve($payload),
            $merge
        );

        $executor = new Executor;
		return $executor->run($pending_request);
    }

    public function perform(string $method, array $data = [], ReplyMarkup|string|null $replyMarkup = null): Response
    {
        $pending_request = new RawPendingRequest(
            $this->endpoint,
            $this->apiKey,
            $method,
            $data,
            $replyMarkup
        );

        $executor = new Executor;
        return $executor->run($pending_request);
    }

	public function async(Closure $closure): array
    {
		/**
		 * @var PendingRequestStack $stack
		 */
		$stack = App::makeWith(PendingRequestStack::class, [
            'endpoint' => $this->endpoint,
            'apiKey' => $this->apiKey
        ]);

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
