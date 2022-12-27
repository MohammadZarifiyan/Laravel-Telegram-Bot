<?php

namespace MohammadZarifiyan\Telegram;

use CURLFile;
use MohammadZarifiyan\Telegram\Interfaces\HasReplyMarkup;
use MohammadZarifiyan\Telegram\Interfaces\Payload;
use MohammadZarifiyan\Telegram\Interfaces\PendingRequest as PendingRequestInterface;

class PendingRequest implements PendingRequestInterface
{
	public function __construct(
		protected string $endpoint,
		protected string $apiKey,
		public Payload $payload,
		public array $merge = [],
	) {
		//
	}
	
	public function getUrl(): string
	{
		return sprintf('%s/bot%s/%s', $this->endpoint, $this->apiKey, $this->payload->method());
	}
	
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
	
	public function getContentType(): string
	{
		foreach ($this->getBody() as $key => $value) {
			if ($value instanceof CURLFile) {
				return 'multipart/form-data';
			}
		}
		
		return 'application/json';
	}
}
