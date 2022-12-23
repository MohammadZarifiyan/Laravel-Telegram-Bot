<?php

namespace MohammadZarifiyan\Telegram;

use MohammadZarifiyan\Telegram\Interfaces\HasReplyMarkup;
use MohammadZarifiyan\Telegram\Interfaces\Payload;

class PendingRequest
{
	public function __construct(
		public Payload $payload,
		public array $merge = [],
		protected ?string $apiKey = null
	) {
		//
	}
	
	/**
	 * Returns URL for pending request.
	 *
	 * @return string
	 */
	public function getUrl(): string
	{
		return sprintf('https://api.telegram.org/bot%s/%s', $this->apiKey, $this->payload->method());
	}
	
	/**
	 * Get request body.
	 *
	 * @return array
	 */
	public function getBody(): array
	{
		return array_merge(
			$this->payload->data(),
			$this->getReplyMarkup(),
			$this->merge
		);
	}
	
	/**
	 * Get payload reply markup.
	 *
	 * @return array
	 */
	public function getReplyMarkup(): array
	{
		if (!($this->payload instanceof HasReplyMarkup)) {
			return [];
		}
		
		$resolved_reply_markup = try_resolve(
			$this->payload->replyMarkup()
		);
		
		if (empty($resolved_reply_markup)) {
			return [];
		}
		
		return [
			'reply_markup' => json_encode($resolved_reply_markup())
		];
	}
}
