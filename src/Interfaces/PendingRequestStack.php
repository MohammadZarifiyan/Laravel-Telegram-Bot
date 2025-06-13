<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

use Illuminate\Contracts\Support\Arrayable;

interface PendingRequestStack extends Arrayable
{
    /**
     * Constructs new instance.
     *
     * @param string|null $endpoint
     * @param string|null $apiKey
     */
	public function __construct(?string $endpoint = null, ?string $apiKey = null);

    /**
     * Creates pending request and adds it to current instance stack.
     *
     * @param string|null $as
     * @return PendingRequestBuilder
     */
	public function add(?string $as = null): PendingRequestBuilder;
}
