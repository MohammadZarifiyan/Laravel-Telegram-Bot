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
     * Creates pending request and adds it to current instance stack.
     *
     * @param string $method
     * @return PendingRequestAdder
     */
	public function add(string $method): PendingRequestAdder;
}
