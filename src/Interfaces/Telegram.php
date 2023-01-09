<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

use Closure;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use MohammadZarifiyan\Telegram\Update;

interface Telegram
{
	/**
	 * Constructs Telegram instance.
	 *
	 * @param string $apiKey
	 * @param string $endpoint
	 */
	public function __construct(string $apiKey, string $endpoint);
	
	/**
	 * Creates new Telegram instance.
	 *
	 * @param string $apiKey
	 * @param string $endpoint
	 * @return $this
	 */
	public function fresh(string $apiKey, string $endpoint): static;
	
	/**
	 * Changes Api key.
	 *
	 * @param string $apiKey
	 * @return $this
	 */
	public function setApiKey(string $apiKey): static;

	/**
	 * Handles request.
	 *
	 * @param Request $request
	 * @return void
	 */
	public function handleRequest(Request $request): void;
	
	/**
	 * Get update if exists.
	 *
	 * @return Update|null
	 */
	public function getUpdate(): ?Update;

	/**
	 * Executes payload.
	 *
	 * @param Payload|string $payload
	 * @param array $merge
	 * @return Response
	 */
    public function execute(Payload|string $payload, array $merge = []): Response;

	/**
	 * Creates and returns a pool for executing payloads.
	 *
	 * @param Closure $closure
	 * @return array<Response>
	 */
    public function executeAsync(Closure $closure): array;
	
	/**
	 * Generates file download URL from file path.
	 *
	 * @param string $filePath
	 * @return string
	 */
	public function generateFileUrl(string $filePath): string;
}
