<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

use Illuminate\Contracts\Support\Arrayable;

interface PendingRequestStack extends Arrayable
{
	/**
	 * Converts payload to pending request and adds it to current instance stack.
	 *
	 * @param Payload|string $payload
	 * @param array $merge
	 * @return $this
	 */
	public function add(Payload|string $payload, array $merge = []): static;
}
