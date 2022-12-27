<?php

namespace MohammadZarifiyan\Telegram;

use MohammadZarifiyan\Telegram\Interfaces\Payload;
use MohammadZarifiyan\Telegram\Interfaces\PendingRequestStack as PendingRequestInterface;

class PendingRequestStack implements PendingRequestInterface
{
	protected array $pendingRequests;
	
	public function __construct(protected string $endpoint, protected string $apiKey)
	{
		//
	}
	
	public function execute(Payload|string $payload, array $merge = []): static
	{
		$this->pendingRequests[] = new PendingRequest(
			$this->endpoint,
			$this->apiKey,
			try_resolve($payload),
			$merge
		);
		
		return $this;
	}
	
	public function toArray(): array
	{
		return $this->pendingRequests;
	}
}
