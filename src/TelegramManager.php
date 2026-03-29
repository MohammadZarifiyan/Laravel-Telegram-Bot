<?php

namespace MohammadZarifiyan\Telegram;

use Closure;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Traits\Macroable;
use MohammadZarifiyan\Telegram\Exceptions\InvalidTelegramBotApiKeyException;
use MohammadZarifiyan\Telegram\Exceptions\TelegramException;
use MohammadZarifiyan\Telegram\Exceptions\TelegramOriginException;
use MohammadZarifiyan\Telegram\Facades\Telegram;
use MohammadZarifiyan\Telegram\Interfaces\MockManager;
use MohammadZarifiyan\Telegram\Interfaces\ReplyMarkup;
use MohammadZarifiyan\Telegram\Interfaces\Telegram as TelegramInterface;
use PHPUnit\Framework\Assert as PHPUnit;

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

    /**
     * @inheritDoc
     */
    public function getUpdate(): Update
    {
        $this->update ??= App::make(Update::class);

        return $this->update;
    }

    /**
     * @throws InvalidTelegramBotApiKeyException
     * @return int
     */
    public function getBotId(): int
    {
        $parseApiKey = Telegram::parseApiKey($this->apiKey);

        return $parseApiKey->botId;
    }

    public function perform(string $method, array $data = [], ReplyMarkup|string|null $replyMarkup = null): Response
    {
        $pendingTelegramRequest = new PendingTelegramRequest(
            $this->endpoint,
            $this->apiKey,
            $method,
            $data,
            $replyMarkup
        );

        $executor = new Executor;
        return $executor->run($pendingTelegramRequest);
    }

	public function concurrent(Closure $closure): array
    {
        $stack = new PendingTelegramRequestStack($this->endpoint, $this->apiKey);
        $closure($stack);

        $executor = new Executor;

        return $executor->runConcurrent($stack->toArray());
    }

    public function validateAuthorizationData(array $authData): bool
    {
        if (!isset($authData['hash']) || !is_string($authData['hash'])) {
            return false;
        }

        $dataCheckString = collect($authData)
            ->except('hash')
            ->sortKeys()
            ->map(fn ($value, $key) => $key . '=' . $value)
            ->implode(PHP_EOL);

        $secretKey = hash('sha256', $this->apiKey, true);
        $knownString = hash_hmac('sha256', $dataCheckString, $secretKey);

        return hash_equals($knownString, $authData['hash']);
    }

    public function validateWebAppInitData(string $initData): bool
    {
        parse_str($initData, $parsedInitData);

        if (!isset($parsedInitData['hash']) || !is_string($parsedInitData['hash'])) {
            return false;
        }

        $dataCheckString = collect($parsedInitData)
            ->except('hash')
            ->sortKeys()
            ->map(fn ($value, $key) => $key . '=' . $value)
            ->implode(PHP_EOL);

        $secretKey = hash_hmac('sha256', $this->apiKey, 'WebAppData', true);
        $knownString = hash_hmac('sha256', $dataCheckString, $secretKey);

        return hash_equals($knownString, $parsedInitData['hash']);
    }

	public function generateFileUrl(string $filePath): string
	{
		return sprintf('%s/file/bot%s/%s', $this->endpoint, $this->apiKey, $filePath);
	}

    public function fake(null|array|Promise $promise = null): void
    {
        /**
         * @var MockManager $mockManager
         */
        $mockManager = App::make(MockManager::class);
        $mockManager->startRecording();

        foreach (Arr::wrap($promise) as $item) {
            $mockManager->addPromise($item);
        }
    }

    public function assertSent(callable $callback): void
    {
        /**
         * @var MockManager $mockManager
         */
        $mockManager = App::make(MockManager::class);

        PHPUnit::assertTrue(
            $mockManager->recorded($callback)->count() > 0,
            'An expected request was not recorded.'
        );
    }

    public function assertSentInOrder(array $callbacks): void
    {
        /**
         * @var MockManager $mockManager
         */
        $mockManager = App::make(MockManager::class);
        $recorded = $mockManager->recorded();

        $this->assertSentCount(count($callbacks));

        foreach ($callbacks as $index => $callback) {
            PHPUnit::assertTrue($callback(
                $recorded[$index][0],
                $recorded[$index][1]
            ), 'An expected request (#'.($index + 1).') was not recorded.');
        }
    }

    public function assertNotSent(callable $callback): void
    {
        /**
         * @var MockManager $mockManager
         */
        $mockManager = App::make(MockManager::class);

        PHPUnit::assertFalse(
            $mockManager->recorded($callback)->count() > 0,
            'Unexpected request was recorded.'
        );
    }

    public function assertNothingSent(): void
    {
        /**
         * @var MockManager $mockManager
         */
        $mockManager = App::make(MockManager::class);

        PHPUnit::assertEmpty(
            $mockManager->recorded()->toArray(),
            'Requests were recorded.'
        );
    }

    public function assertSentCount(int $count): void
    {
        /**
         * @var MockManager $mockManager
         */
        $mockManager = App::make(MockManager::class);

        PHPUnit::assertCount(
            $count,
            $mockManager->recorded()->toArray(),
        );
    }
}
