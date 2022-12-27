<?php

namespace MohammadZarifiyan\Telegram;

use Illuminate\Support\Facades\App;
use MohammadZarifiyan\Telegram\Interfaces\Payload;
use MohammadZarifiyan\Telegram\Interfaces\PendingRequest;
use MohammadZarifiyan\Telegram\Interfaces\PendingRequestStack as PendingRequestInterface;

class PendingRequestStack implements PendingRequestInterface
{
	protected array $pendingRequests;
	
	public function __construct(protected string $endpoint, protected string $apiKey)
	{
		//
	}
	
	public function add(Payload|string $payload, array $merge = []): static
	{
		$this->pendingRequests[] = App::makeWith(PendingRequest::class, [
			'endpoint' => $this->endpoint,
			'apiKey' => $this->apiKey,
			'payload' => try_resolve($payload),
			'merge' => $merge
		]);
		
		return $this;
	}
	
	public function toArray(): array
	{
		return $this->pendingRequests;
	}
}
