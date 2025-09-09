<?php

namespace MohammadZarifiyan\Telegram;

use Closure;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Traits\Macroable;
use MohammadZarifiyan\Telegram\Exceptions\InvalidTelegramBotApiKeyException;
use MohammadZarifiyan\Telegram\Interfaces\Telegram as TelegramInterface;
use MohammadZarifiyan\Telegram\Interfaces\ReplyMarkup;
use MohammadZarifiyan\Telegram\Exceptions\TelegramException;
use MohammadZarifiyan\Telegram\Exceptions\TelegramOriginException;
use MohammadZarifiyan\Telegram\Interfaces\PendingRequestStack;

class TelegramManager implements TelegramInterface
{
	use Macroable;
	
	protected ?Update $update;
	
	public function __construct(
        protected ?string $apiKey = null,
        protected ?string $endpoint = null,
        protected ?string $secretToken = null
    ) {
		//
	}
	
	public function setApiKey(?string $apiKey = null): static
	{
		$this->apiKey = $apiKey;
		
		return $this;
	}

	public function setEndpoint(?string $endpoint = null): static
	{
		$this->endpoint = $endpoint;

		return $this;
	}

	public function setSecretToken(?string $secretToken = null): static
	{
		$this->secretToken = $secretToken;

		return $this;
	}

	/**
	 * @throws TelegramException
	 * @throws TelegramOriginException|\ReflectionException
	 */
	public function handleRequest(Request $request): void
	{
        $this->update = $request instanceof Update ? $request : Update::createFrom($request);
		$updateHandler = new UpdateHandler($this->update, $this->secretToken);

		foreach ($updateHandler->run() as $update) {
			$this->update = $update;
		}
	}

	public function getUpdate(): ?Update
	{
		return $this->update ?? null;
	}

    /**
     * @throws InvalidTelegramBotApiKeyException
     * @return int
     */
    public function getBotId(): int
    {
        $parts = explode(':', $this->apiKey);

        if (count($parts) === 2 && is_numeric($parts[0])) {
            return (int) $parts[0];
        }

        throw new InvalidTelegramBotApiKeyException('API Key is not valid');
    }

    public function perform(string $method, array $data = [], ReplyMarkup|string|null $replyMarkup = null): Response
    {
        $pendingRequest = new PendingRequest(
            $this->endpoint,
            $this->apiKey,
            $method,
            $data,
            $replyMarkup
        );

        $executor = new Executor;
        return $executor->run($pendingRequest);
    }

	public function concurrent(Closure $closure): array
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

		return $executor->runConcurrent(
			$stack->toArray()
		);
    }

    public function verifyContentHash(array|string $content): bool
    {
        if (is_array($content)) {
            $data = $content;
        }
        else if (json_validate($content)) {
            $data = json_decode($content);
        }
        else {
            parse_str($content, $data);
        }

        if (empty($data['hash'])) {
            return false;
        }

        $authData = collect($data)
            ->except(['hash', 'signature'])
            ->sortKeys()
            ->map(fn ($value, $key) => $key . '=' . $value)
            ->sort()
            ->implode(PHP_EOL);

        $secretKey = hash('sha256', $this->apiKey, true);
        $calculatedHash = hash_hmac('sha256', $authData, $secretKey);

        return hash_equals($data['hash'], $calculatedHash);
    }

	public function generateFileUrl(string $filePath): string
	{
		return sprintf('%s/file/bot%s/%s', $this->endpoint, $this->apiKey, $filePath);
	}
}
