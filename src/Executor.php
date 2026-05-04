<?php

namespace MohammadZarifiyan\Telegram;

use Closure;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use MohammadZarifiyan\Telegram\Events\ConnectionFailed;
use MohammadZarifiyan\Telegram\Events\ProxyFailed;
use MohammadZarifiyan\Telegram\Events\ProxyUsed;
use MohammadZarifiyan\Telegram\Events\RequestSending;
use MohammadZarifiyan\Telegram\Events\ResponseReceived;
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
		$pendingHttpRequest = $this->buildPendingHttpRequest($pendingTelegramRequest);
		$proxy = $this->getNextProxy();

		RequestSending::dispatch($pendingTelegramRequest, $pendingHttpRequest);

        try {
			if ($this->mockManager->isRecording()) {
				$promisedHttpResponse = $this->mockManager->promisedHttpResponse($pendingTelegramRequest->apiKey, $pendingTelegramRequest->endpoint, $pendingTelegramRequest->method);
				$response = is_null($promisedHttpResponse) ? $this->getResponse($pendingHttpRequest, $proxy) : $promisedHttpResponse;

				$this->mockManager->pair($pendingTelegramRequest, $response);
			}
			else {
				$response = $this->getResponse($pendingHttpRequest, $proxy);
			}

			ResponseReceived::dispatch($pendingTelegramRequest, $pendingHttpRequest, $response);
			ProxyUsed::dispatchUnless(is_null($proxy), $proxy);
		}
        catch (ConnectionException $exception) {
			ConnectionFailed::dispatch($pendingTelegramRequest, $pendingHttpRequest, $exception);
            ProxyFailed::dispatchUnless(is_null($proxy), $proxy);

            throw $exception;
        }

        $response->throwIf(
			config('telegram.throw-http-exception')
		);

        return $response;
	}

	public function runConcurrent(array $pendingTelegramRequests): array
	{
		$pendingTelegramRequests = new Collection($pendingTelegramRequests);
		$pendingHttpRequests = $pendingTelegramRequests->map($this->buildPendingHttpRequest(...));
        $proxies = $pendingTelegramRequests->map($this->getNextProxy(...));

		return $pendingTelegramRequests->keys()
			->each(function ($key) use ($pendingTelegramRequests, $pendingHttpRequests) {
				RequestSending::dispatch($pendingTelegramRequests[$key], $pendingHttpRequests[$key]);
			})
			->when(
				$this->mockManager->isRecording(),
				function (Collection $keys) use ($pendingTelegramRequests, $pendingHttpRequests, $proxies) {
					$mockedHttpResponses = new Collection;
					$pendingHttpRequestsWithoutMock = new Collection;

					foreach ($keys as $key) {
						$promisedHttpResponse = $this->mockManager->promisedHttpResponse(
							$pendingTelegramRequests[$key]->apiKey,
							$pendingTelegramRequests[$key]->endpoint,
							$pendingTelegramRequests[$key]->method
						);

						if (is_null($promisedHttpResponse)) {
							$pendingHttpRequestsWithoutMock[$key] = $pendingHttpRequests[$key];
						}
						else {
							$mockedHttpResponses[$key] = $promisedHttpResponse;
						}
					}

					$pollHttpResponses = Http::pool($this->getPoolRequestClosure($pendingHttpRequestsWithoutMock, $proxies));
					$combinedHttpResponses = $mockedHttpResponses->union($pollHttpResponses);

					foreach ($keys as $key) {
						$this->mockManager->pair($pendingTelegramRequests[$key], $combinedHttpResponses[$key]);
					}

					return $keys->map(fn ($value, $key) => $combinedHttpResponses[$key]);
				},
				function () use ($pendingHttpRequests, $proxies) {
					$responses = Http::pool($this->getPoolRequestClosure($pendingHttpRequests, $proxies));

					return new Collection($responses);
				}
			)
			->each(function ($response, $key) use ($pendingTelegramRequests, $pendingHttpRequests) {
				if ($response instanceof ConnectException) {
					ConnectionFailed::dispatch($pendingTelegramRequests[$key], $pendingHttpRequests[$key], $response);
				}
				else if ($response instanceof Response) {
					ResponseReceived::dispatch($pendingTelegramRequests[$key], $pendingHttpRequests[$key], $response);
				}
			})
			->each(function ($response, $key) use ($proxies) {
				if ($proxies[$key] instanceof Proxy === false) {
					return;
				}

				if ($response instanceof ConnectionException) {
					ProxyFailed::dispatch($proxies[$key]);
				}
				else if ($response instanceof Response) {
					ProxyUsed::dispatch($proxies[$key]);
				}
			})
			->toArray();
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

	protected function getPoolRequestClosure(Collection $pendingHttpRequests, Collection $proxies): Closure
	{
		return function (Pool $pool) use ($pendingHttpRequests, $proxies) {
			foreach ($pendingHttpRequests as $as => $pendingHttpRequest) {
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
