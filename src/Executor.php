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
use MohammadZarifiyan\Telegram\Interfaces\PendingRequest as PendingRequestInterface;
use MohammadZarifiyan\Telegram\Interfaces\ProxyRepository;

class Executor
{
    protected Collection $proxyList;
    protected array $retry;
    protected bool $verifyEndpoint;
    protected ?string $requestManipulator;

    public function __construct()
    {
        /**
         * @var ProxyRepository $proxyRepository
         */
        $proxyRepository = App::make(ProxyRepository::class);
        $this->proxyList = $proxyRepository->get();

        $this->retry = config('telegram.retry');
        $this->verifyEndpoint = config('telegram.verify-endpoint');
        $this->requestManipulator = config('telegram.pending-request-manipulator');
    }

	public function run(PendingRequestInterface $pendingRequest): Response
	{
        $proxy = $this->getProxy();
        $throwHttpException = config('telegram.throw-http-exception');
        $finalPendingRequest = empty($this->requestManipulator) ? $pendingRequest : new $this->requestManipulator($pendingRequest);
        $data = $this->getData($finalPendingRequest);

        try {
            $response = Http::acceptJson()
                ->attach($finalPendingRequest->getAttachments())
                ->unless(
                    is_null($proxy),
                    fn ($pendingRequest) => $pendingRequest->withOptions(['proxy' => $proxy->configuration])
                )
                ->unless($this->verifyEndpoint, fn ($pendingRequest) => $pendingRequest->withoutVerifying())
                ->retry(
                    $this->retry['times'],
                    $this->retry['sleep'],
                    fn ($exception, $request) => $exception instanceof ConnectionException,
                    false
                )
                ->post($finalPendingRequest->getUrl(), $data);
        }
        catch (ConnectionException $exception) {
            ProxyFailed::dispatchUnless(is_null($proxy), $proxy);

            throw $exception;
        }

        ProxyUsed::dispatchUnless(is_null($proxy), $proxy);

        $response->throwIf($throwHttpException);

        return $response;
	}

	public function runConcurrent(array $pendingRequests): array
	{
        $proxies = [];

		$responses = Http::pool(function (Pool $pool) use ($pendingRequests, &$proxies) {
            foreach ($pendingRequests as $as => $pendingRequest) {
                $proxies[$as] = $this->getProxy();
                $finalPendingRequest = empty($this->requestManipulator) ? $pendingRequest : new $this->requestManipulator($pendingRequest);
                $pendingClientRequest = is_null($as) ? $pool->acceptJson() : $pool->as($as)->acceptJson();

                $pendingClientRequest
                    ->attach($finalPendingRequest->getAttachments())
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
                    ->post($finalPendingRequest->getUrl(), $this->getData($finalPendingRequest));
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

    protected function getData(PendingRequestInterface $pendingRequest): array
    {
        $attachments = $pendingRequest->getAttachments();

        if (count($attachments) === 0) {
            return $pendingRequest->getBody();
        }

        return array_map(
            fn ($item) => is_array($item) ? json_encode($item) : $item,
            $pendingRequest->getBody()
        );
    }

    protected function getProxy(): ?Proxy
    {
        static $index = 0;
        $proxy = $this->proxyList[$index] ?? null;
        $index = $index + 1 < $this->proxyList->count() ? $index + 1 : 0;

        return $proxy;
    }
}
