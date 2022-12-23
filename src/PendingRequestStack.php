<?php

namespace MohammadZarifiyan\Telegram;

use MohammadZarifiyan\Telegram\Interfaces\Payload;
use MohammadZarifiyan\Telegram\Interfaces\PendingRequestStack as PendingRequestInterface;

class PendingRequestStack implements PendingRequestInterface
{
	protected array $pendingRequests;
	
	public function add(Payload|string $payload, array $merge = []): static
	{
		$this->pendingRequests[] = new PendingRequest(
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
