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
        $request_manipulator = config('telegram.pending-request-manipulator');
        $final_pending_request = empty($request_manipulator) ? $pendingRequest : new $request_manipulator($pendingRequest);
        $verify_endpoint = config('telegram.verify-endpoint');

		return Http::throwIf(
			config('telegram.throw-http-exception')
		)
			->acceptJson()
            ->attach($final_pending_request->getAttachments())
            ->when(!$verify_endpoint, fn ($pendingRequest) => $pendingRequest->withoutVerifying())
			->retry(
				5,
				100,
				fn ($exception, $request) => $exception instanceof ConnectionException,
				config('telegram.throw-http-exception')
			)
			->post($final_pending_request->getUrl(), $final_pending_request->getBody());
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
                    ->post($final_pending_request->getUrl(), $final_pending_request->getBody());
            }
        });
	}
}
