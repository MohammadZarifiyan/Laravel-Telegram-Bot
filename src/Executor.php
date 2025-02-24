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
         * @var ProxyRepository $proxy_repository
         */
        $proxy_repository = App::make(ProxyRepository::class);
        $this->proxyList = $proxy_repository->get();
    }

	public function run(PendingRequestInterface $pendingRequest): Response
	{
        $proxy = $this->getProxy();
        $retry = config('telegram.retry');
        $verify_endpoint = config('telegram.verify-endpoint');
        $throw_http_exception = config('telegram.throw-http-exception');
        $request_manipulator = config('telegram.pending-request-manipulator');
        $final_pending_request = empty($request_manipulator) ? $pendingRequest : new $request_manipulator($pendingRequest);
        $data = $this->getData($final_pending_request);

        try {
            $response = Http::acceptJson()
                ->attach($final_pending_request->getAttachments())
                ->when(
                    $proxy instanceof Proxy,
                    fn ($pendingRequest) => $pendingRequest->withOptions(['proxy' => $proxy->getConfiguration()])
                )
                ->when(!$verify_endpoint, fn ($pendingRequest) => $pendingRequest->withoutVerifying())
                ->retry(
                    $retry['times'],
                    $retry['sleep'],
                    fn ($exception, $request) => $exception instanceof ConnectionException,
                    false
                )
                ->post($final_pending_request->getUrl(), $data);
        }
        catch (ConnectionException $exception) {
            ProxyFailed::dispatchIf($proxy instanceof Proxy, $proxy);

            throw $exception;
        }

        ProxyUsed::dispatchIf($proxy instanceof Proxy, $proxy);

        $response->throwIf($throw_http_exception);

        return $response;
	}

	public function runConcurrent(array $pendingRequests): array
	{
        $proxies = [];

		$responses = Http::pool(function (Pool $pool) use ($pendingRequests, &$proxies) {
            $request_manipulator = config('telegram.pending-request-manipulator');
            $verify_endpoint = config('telegram.verify-endpoint');
            $retry = config('telegram.retry');

            foreach ($pendingRequests as $index => $pendingRequest) {
                $proxies[$index] = $this->getProxy();
                $final_pending_request = empty($request_manipulator) ? $pendingRequest : new $request_manipulator($pendingRequest);

                $pool->acceptJson()
                    ->attach($final_pending_request->getAttachments())
                    ->when(
                        $proxies[$index] instanceof Proxy,
                        fn ($pendingRequest) => $pendingRequest->withOptions(['proxy' => $proxies[$index]->getConfiguration()])
                    )
                    ->when(!$verify_endpoint, fn ($pendingRequest) => $pendingRequest->withoutVerifying())
                    ->retry(
                        $retry['times'],
                        $retry['sleep'],
                        fn ($exception, $request) => $exception instanceof ConnectionException,
                        false
                    )
                    ->post($final_pending_request->getUrl(), $this->getData($final_pending_request));
            }
        });

        foreach ($responses as $index => $response) {
            if ($proxies[$index] instanceof Proxy === false) {
                continue;
            }

            if ($response instanceof ConnectionException) {
                ProxyFailed::dispatch($proxies[$index]);
            }
            else if ($response instanceof Response) {
                ProxyUsed::dispatch($proxies[$index]);
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
