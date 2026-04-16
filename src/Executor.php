<?php

namespace MohammadZarifiyan\Telegram;

use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use MohammadZarifiyan\Telegram\Events\ProxyFailed;
use MohammadZarifiyan\Telegram\Events\ProxyUsed;
use MohammadZarifiyan\Telegram\Interfaces\PendingHttpRequest as PendingHttpRequestInterface;
use MohammadZarifiyan\Telegram\Interfaces\ProxyRepository;
use MohammadZarifiyan\Telegram\Interfaces\MockManager;

class Executor
{
    protected MockManager $mockManager;
    protected Collection $proxyList;
    protected array $retry;
    protected bool $verifyEndpoint;
    protected ?string $httpRequestManipulator;

    public function __construct()
    {
        $this->mockManager = App::make(MockManager::class);

        /**
         * @var ProxyRepository $proxyRepository
         */
        $proxyRepository = App::make(ProxyRepository::class);
        $this->proxyList = $proxyRepository->get();

        $this->retry = config('telegram.retry');
        $this->verifyEndpoint = config('telegram.verify-endpoint');
        $this->httpRequestManipulator = config('telegram.pending-http-request-manipulator');
    }

	public function run(PendingTelegramRequest $pendingTelegramRequest): Response
	{
        $proxy = $this->getNextProxy();
        $throwHttpException = config('telegram.throw-http-exception');
        $pendingHttpRequest = $this->buildPendingHttpRequest($pendingTelegramRequest);

        if ($this->mockManager->isRecording()) {
            $promisedHttpResponse = $this->mockManager->promisedHttpResponse($pendingTelegramRequest->apiKey, $pendingTelegramRequest->endpoint, $pendingTelegramRequest->method);

			if ($promisedHttpResponse instanceof Promise) {
				$this->mockManager->pair($pendingTelegramRequest, $promisedHttpResponse);

				ProxyUsed::dispatchUnless(is_null($proxy), $proxy);

				return $promisedHttpResponse;
			}

			try {
				$response = $this->getResponse($pendingHttpRequest, $proxy);

				$this->mockManager->pair($pendingTelegramRequest, $response);
			}
			catch (ConnectionException $exception) {
				ProxyFailed::dispatchUnless(is_null($proxy), $proxy);

				throw $exception;
			}

			ProxyUsed::dispatchUnless(is_null($proxy), $proxy);

			$response->throwIf($throwHttpException);

			return $response;
        }

        try {
            $response = $this->getResponse($pendingHttpRequest, $proxy);
        }
        catch (ConnectionException $exception) {
            ProxyFailed::dispatchUnless(is_null($proxy), $proxy);

            throw $exception;
        }

        ProxyUsed::dispatchUnless(is_null($proxy), $proxy);

        $response->throwIf($throwHttpException);

        return $response;
	}

	public function runConcurrent(array $pendingTelegramRequests): array
	{
		$pendingTelegramRequests = new Collection($pendingTelegramRequests);
        $proxies = $pendingTelegramRequests->map($this->getNextProxy(...));

        if ($this->mockManager->isRecording()) {
			$mockedHttpResponses = new Collection;
			$telegramRequestsWithoutMock = new Collection;

			foreach ($pendingTelegramRequests as $key => $pendingTelegramRequest) {
				$promisedHttpResponse = $this->mockManager->promisedHttpResponse(
					$pendingTelegramRequest->apiKey,
					$pendingTelegramRequest->endpoint,
					$pendingTelegramRequest->method
				);

				if (is_null($promisedHttpResponse)) {
					$telegramRequestsWithoutMock[$key] = $pendingTelegramRequest;
				}
				else {
					$mockedHttpResponses[$key] = $promisedHttpResponse;
				}
			}

			$pollHttpResponses = Http::pool($this->getPoolRequestClosure($telegramRequestsWithoutMock, $proxies));
			$combinedHttpResponses = $mockedHttpResponses->union($pollHttpResponses);

			foreach ($pendingTelegramRequests as $key => $pendingTelegramRequest) {
				$this->mockManager->pair($pendingTelegramRequest, $combinedHttpResponses[$key]);

				if ($proxies[$key] instanceof Proxy === false) {
					continue;
				}

				if ($combinedHttpResponses[$key] instanceof ConnectionException) {
					ProxyFailed::dispatch($proxies[$key]);
				}
				else if ($combinedHttpResponses[$key] instanceof Response) {
					ProxyUsed::dispatch($proxies[$key]);
				}
			}

			return $pendingTelegramRequests->map(fn ($value, $key) => $combinedHttpResponses[$key])->toArray();
        }

		$responses = Http::pool($this->getPoolRequestClosure($pendingTelegramRequests, $proxies));

		foreach ($responses as $as => $response) {
			if ($proxies[$as] instanceof Proxy === false) {
				continue;
			}

			if ($response instanceof ConnectionException) {
				ProxyFailed::dispatch($proxies[$as]);
			}
			else if ($response instanceof Response) {
				ProxyUsed::dispatch($proxies[$as]);
			}
		}

        return $responses;
	}

    protected function buildPendingHttpRequest(PendingTelegramRequest $pendingTelegramRequest): PendingHttpRequestInterface
    {
        $pendingHttpRequest = new PendingHttpRequest($pendingTelegramRequest);

        if (is_null($this->httpRequestManipulator)) {
            return $pendingHttpRequest;
        }

        return new $this->httpRequestManipulator($pendingHttpRequest);
    }

    protected function getPendingHttpRequestData(PendingHttpRequestInterface $pendingHttpRequest): array
    {
        $attachments = $pendingHttpRequest->getAttachments();

        if (count($attachments) === 0) {
            return $pendingHttpRequest->getBody();
        }

        return array_map(
            fn ($item) => is_array($item) ? json_encode($item) : $item,
            $pendingHttpRequest->getBody()
        );
    }

    protected function getNextProxy(): ?Proxy
    {
        static $index = 0;
        $proxy = $this->proxyList[$index] ?? null;
        $index = $index + 1 < $this->proxyList->count() ? $index + 1 : 0;

        return $proxy;
    }

	protected function getResponse(PendingHttpRequestInterface $pendingHttpRequest, ?Proxy $proxy): Response
	{
		$data = $this->getPendingHttpRequestData($pendingHttpRequest);

		return Http::acceptJson()
			->attach($pendingHttpRequest->getAttachments())
			->unless(
				is_null($proxy),
				fn (PendingRequest $pendingRequest) => $pendingRequest->withOptions(['proxy' => $proxy->configuration])
			)
			->unless($this->verifyEndpoint, fn (PendingRequest $pendingRequest) => $pendingRequest->withoutVerifying())
			->retry(
				$this->retry['times'],
				$this->retry['sleep'],
				fn ($exception, $request) => $exception instanceof ConnectionException,
				false
			)
			->post($pendingHttpRequest->getUrl(), $data);
	}

	protected function getPoolRequestClosure(Collection $pendingTelegramRequests, Collection $proxies): Closure
	{
		return function (Pool $pool) use ($pendingTelegramRequests, $proxies) {
			foreach ($pendingTelegramRequests as $as => $pendingTelegramRequest) {
				$pendingHttpRequest = $this->buildPendingHttpRequest($pendingTelegramRequest);

				$pool->as($as)
					->acceptJson()
					->attach($pendingHttpRequest->getAttachments())
					->unless(
						is_null($proxies[$as]),
						fn (PendingRequest $pendingRequest) => $pendingRequest->withOptions(['proxy' => $proxies[$as]->configuration])
					)
					->unless($this->verifyEndpoint, fn (PendingRequest $pendingRequest) => $pendingRequest->withoutVerifying())
					->retry(
						$this->retry['times'],
						$this->retry['sleep'],
						fn ($exception, $request) => $exception instanceof ConnectionException,
						false
					)
					->post($pendingHttpRequest->getUrl(), $this->getPendingHttpRequestData($pendingHttpRequest));
			}
		};
	}
}
