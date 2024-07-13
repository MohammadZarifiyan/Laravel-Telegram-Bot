<?php

namespace MohammadZarifiyan\Telegram;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use MohammadZarifiyan\Telegram\Interfaces\PendingRequest as PendingRequestInterface;

class Executor
{
	public function run(PendingRequestInterface $pendingRequest): Response
	{
        $verify_endpoint = config('telegram.verify-endpoint');
        $throw_http_exception = config('telegram.throw-http-exception');
        $request_manipulator = config('telegram.pending-request-manipulator');
        $final_pending_request = empty($request_manipulator) ? $pendingRequest : new $request_manipulator($pendingRequest);
        $data = $this->getData($final_pending_request);

        return Http::throwIf($throw_http_exception)
			->acceptJson()
            ->attach($final_pending_request->getAttachments())
            ->when(!$verify_endpoint, fn ($pendingRequest) => $pendingRequest->withoutVerifying())
			->retry(
				5,
				100,
				fn ($exception, $request) => $exception instanceof ConnectionException,
                $throw_http_exception
			)
			->post($final_pending_request->getUrl(), $data);
	}
	
	public function runConcurrent(array $pendingRequests): array
	{
        $request_manipulator = config('telegram.pending-request-manipulator');
        $verify_endpoint = config('telegram.verify-endpoint');

		return Http::pool(function (Pool $pool) use ($pendingRequests, $request_manipulator, $verify_endpoint) {
            foreach ($pendingRequests as $pendingRequest) {
                $final_pending_request = empty($request_manipulator) ? $pendingRequest : new $request_manipulator($pendingRequest);

                $pool->acceptJson()
                    ->attach($final_pending_request->getAttachments())
                    ->when(!$verify_endpoint, fn ($pendingRequest) => $pendingRequest->withoutVerifying())
                    ->retry(
                        5,
                        100,
                        fn ($exception, $request) => $exception instanceof ConnectionException,
                        false
                    )
                    ->post($final_pending_request->getUrl(), $this->getData($final_pending_request));
            }
        });
	}

    public function getData(PendingRequestInterface $pendingRequest): array
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
}
