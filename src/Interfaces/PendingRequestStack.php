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
     * Converts data to pending request and adds it to current instance stack.
     *
     * @param string $method
     * @param array $data
     * @param ReplyMarkup|string|null $replyMarkup
     * @param string|null $apiKey
     * @param string|null $endpoint
     * @return static
     */
	public function add(string $method, array $data = [], ReplyMarkup|string|null $replyMarkup = null, string $apiKey = null, string $endpoint = null): static;
}
