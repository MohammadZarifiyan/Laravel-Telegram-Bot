<?php

namespace MohammadZarifiyan\Telegram;

use MohammadZarifiyan\Telegram\Abstractions\PendingRequest;
use MohammadZarifiyan\Telegram\Interfaces\HasReplyMarkup;
use MohammadZarifiyan\Telegram\Interfaces\Payload;

class PayloadPendingRequest extends PendingRequest
{
    public array $content;

	public function __construct(
		protected string $endpoint,
		protected string $apiKey,
		public Payload $payload,
		array $merge = [],
	) {
        $this->content = array_merge(
            $this->payload->data(),
            $this->getReplyMarkup(),
            $merge
        );
	}
	
	public function getUrl(): string
	{
		return sprintf('%s/bot%s/%s', $this->endpoint, $this->apiKey, $this->payload->method());
	}

    public function getContent(): array
    {
        return $this->content;
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
