<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

interface PendingRequest
{
	/**
	 * Constructs a new instance.
	 *
	 * @param string $endpoint
	 * @param string $apiKey
	 * @param Payload $payload
	 * @param array $merge
	 */
	public function __construct(string $endpoint, string $apiKey, Payload $payload, array $merge = []);
	
	/**
	 * Returns URL for pending request.
	 *
	 * @return string
	 */
	public function getUrl(): string;
	
	/**
	 * Get request body.
	 *
	 * @return array
	 */
	public function getBody(): array;
	
	/**
	 * Get request attachments
	 *
	 * @return array
	 */
	public function getAttachments(): array;
}