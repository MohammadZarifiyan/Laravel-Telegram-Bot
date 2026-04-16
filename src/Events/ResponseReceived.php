<?php

namespace MohammadZarifiyan\Telegram\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Client\Response;
use MohammadZarifiyan\Telegram\PendingHttpRequest;
use MohammadZarifiyan\Telegram\PendingTelegramRequest;

class ResponseReceived
{
	use Dispatchable;

	public function __construct(
		public PendingTelegramRequest $pendingTelegramRequest,
		public PendingHttpRequest $pendingHttpRequest,
		public Response $response
	) {
		//
	}
}