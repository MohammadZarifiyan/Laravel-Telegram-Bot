<?php

namespace MohammadZarifiyan\Telegram;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class Executor
{
	public function run(PendingRequest $pendingRequest): Response
	{
		return Http::throwIf(
			config('telegram.throw-http-execution')
		)
			->acceptJson()
			->retry(5, 100, fn ($exception, $request) => $exception instanceof ConnectionException)
			->post($pendingRequest->getUrl(), $pendingRequest->getBody());
	}
	
	public function runAsync(array $pendingRequests): array
	{
		$throw = config('telegram.throw-http-execution');
		
		return Http::pool(
			fn (Pool $pool) => array_map(
				fn (PendingRequest $pendingRequest) => $pool->throwIf($throw)
					->acceptJson()
					->retry(5, 100, fn ($exception, $request) => $exception instanceof ConnectionException)
					->post($pendingRequest->getUrl(), $pendingRequest->getBody()),
				$pendingRequests
			)
		);
	}
}
