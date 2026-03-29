<?php

namespace MohammadZarifiyan\Telegram;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use MohammadZarifiyan\Telegram\Events\ProxyFailed;
use MohammadZarifiyan\Telegram\Events\ProxyUsed;
use MohammadZarifiyan\Telegram\Interfaces\PendingHttpRequest as PendingHttpRequestInterface;
use MohammadZarifiyan\Telegram\Interfaces\ProxyRepository;

class Executor
{
    protected Collection $proxyList;
    protected array $retry;
    protected bool $verifyEndpoint;
    protected ?string $httpRequestManipulator;

    public function __construct()
    {
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
        $data = $this->getPendingHttpRequestData($pendingHttpRequest);

        try {
            $response = Http::acceptJson()
                ->attach($pendingHttpRequest->getAttachments())
                ->unless(
                    is_null($proxy),
                    fn ($pendingClientRequest) => $pendingClientRequest->withOptions(['proxy' => $proxy->configuration])
                )
                ->unless($this->verifyEndpoint, fn ($pendingClientRequest) => $pendingClientRequest->withoutVerifying())
                ->retry(
                    $this->retry['times'],
                    $this->retry['sleep'],
                    fn ($exception, $request) => $exception instanceof ConnectionException,
                    false
                )
                ->post($pendingHttpRequest->getUrl(), $data);
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
        $proxies = [];

		$responses = Http::pool(function (Pool $pool) use ($pendingTelegramRequests, &$proxies) {
            foreach ($pendingTelegramRequests as $as => $pendingTelegramRequest) {
                $proxies[$as] = $this->getNextProxy();
                $pendingHttpRequest = $this->buildPendingHttpRequest($pendingTelegramRequest);
                $pendingClientRequest = is_null($as) ? $pool->acceptJson() : $pool->as($as)->acceptJson();

                $pendingClientRequest
                    ->attach($pendingHttpRequest->getAttachments())
                    ->unless(
                        is_null($proxies[$as]),
                        fn ($pendingClientRequest) => $pendingClientRequest->withOptions(['proxy' => $proxies[$as]->configuration])
                    )
                    ->unless($this->verifyEndpoint, fn ($pendingClientRequest) => $pendingClientRequest->withoutVerifying())
                    ->retry(
                        $this->retry['times'],
                        $this->retry['sleep'],
                        fn ($exception, $request) => $exception instanceof ConnectionException,
                        false
                    )
                    ->post($pendingHttpRequest->getUrl(), $this->getPendingHttpRequestData($pendingHttpRequest));
            }
        });

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
}
