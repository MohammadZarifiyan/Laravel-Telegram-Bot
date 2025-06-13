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
use MohammadZarifiyan\Telegram\Interfaces\Proxy;
use MohammadZarifiyan\Telegram\Interfaces\ProxyRepository;

class Executor
{
    private Collection $proxyList;

    public function __construct()
    {
        /**
         * @var ProxyRepository $proxyRepository
         */
        $proxyRepository = App::make(ProxyRepository::class);
        $this->proxyList = $proxyRepository->get();
    }

	public function run(PendingRequestInterface $pendingRequest): Response
	{
        $proxy = $this->getProxy();
        $retry = config('telegram.retry');
        $verifyEndpoint = config('telegram.verify-endpoint');
        $throwHttpException = config('telegram.throw-http-exception');
        $requestManipulator = config('telegram.pending-request-manipulator');
        $finalPendingRequest = empty($requestManipulator) ? $pendingRequest : new $requestManipulator($pendingRequest);
        $data = $this->getData($finalPendingRequest);

        try {
            $response = Http::acceptJson()
                ->attach($finalPendingRequest->getAttachments())
                ->when(
                    $proxy instanceof Proxy,
                    fn ($pendingRequest) => $pendingRequest->withOptions(['proxy' => $proxy->getConfiguration()])
                )
                ->unless($verifyEndpoint, fn ($pendingRequest) => $pendingRequest->withoutVerifying())
                ->retry(
                    $retry['times'],
                    $retry['sleep'],
                    fn ($exception, $request) => $exception instanceof ConnectionException,
                    false
                )
                ->post($finalPendingRequest->getUrl(), $data);
        }
        catch (ConnectionException $exception) {
            ProxyFailed::dispatchIf($proxy instanceof Proxy, $proxy);

            throw $exception;
        }

        ProxyUsed::dispatchIf($proxy instanceof Proxy, $proxy);

        $response->throwIf($throwHttpException);

        return $response;
	}

	public function runConcurrent(array $pendingRequests): array
	{
        $proxies = [];

		$responses = Http::pool(function (Pool $pool) use ($pendingRequests, &$proxies) {
            $requestManipulator = config('telegram.pending-request-manipulator');
            $verifyEndpoint = config('telegram.verify-endpoint');
            $retry = config('telegram.retry');

            foreach ($pendingRequests as $as => $pendingRequest) {
                $proxies[$as] = $this->getProxy();
                $finalPendingRequest = empty($requestManipulator) ? $pendingRequest : new $requestManipulator($pendingRequest);
                $pendingClientRequest = is_string($as) ? $pool->as($as)->acceptJson() : $pool->acceptJson();

                $pendingClientRequest
                    ->attach($finalPendingRequest->getAttachments())
                    ->when(
                        $proxies[$as] instanceof Proxy,
                        fn ($pendingClientRequest) => $pendingClientRequest->withOptions(['proxy' => $proxies[$as]->getConfiguration()])
                    )
                    ->unless($verifyEndpoint, fn ($pendingClientRequest) => $pendingClientRequest->withoutVerifying())
                    ->retry(
                        $retry['times'],
                        $retry['sleep'],
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

    private function getData(PendingRequestInterface $pendingRequest): array
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

    private function getProxy(): ?Proxy
    {
        static $index = 0;
        $proxy = $this->proxyList[$index] ?? null;
        $index = $index + 1 < $this->proxyList->count() ? $index + 1 : 0;

        return $proxy;
    }
}
