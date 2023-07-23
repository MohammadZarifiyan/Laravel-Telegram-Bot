<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

use Illuminate\Contracts\Support\Arrayable;

interface PendingRequestStack extends Arrayable
{
	/**
	 * Constructs new instance.
	 *
	 * @param string $endpoint
	 * @param string $apiKey
	 */
	public function __construct(string $endpoint, string $apiKey);

    /**
     * Converts payload to pending request and adds it to current instance stack.
     *
     * @param Payload|string $payload
     * @param array $merge
     * @param string|null $apiKey
     * @param string|null $endpoint
     * @return $this
     */
	public function add(Payload|string $payload, array $merge = [], string $apiKey = null, string $endpoint = null): static;
}
