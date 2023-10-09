<?php

namespace MohammadZarifiyan\Telegram;

use MohammadZarifiyan\Telegram\Abstractions\PendingRequest;
use MohammadZarifiyan\Telegram\Interfaces\ReplyMarkup;

class RawPendingRequest extends PendingRequest
{
    public array $content;

	public function __construct(
		protected string $endpoint,
		protected string $apiKey,
        public string $method,
        public array $data = [],
        public ReplyMarkup|string|null $replyMarkup = null
	) {
		$this->content = array_merge(
            $this->data,
            $this->getReplyMarkup(),
        );
	}
	
	public function getUrl(): string
	{
		return sprintf('%s/bot%s/%s', $this->endpoint, $this->apiKey, $this->method);
	}

    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * Get reply markup.
     *
     * @return array
     */
    public function getReplyMarkup(): array
    {
        $resolved_reply_markup = try_resolve($this->replyMarkup);

        if (empty($resolved_reply_markup)) {
            return [];
        }

        return [
            'reply_markup' => json_encode($resolved_reply_markup())
        ];
    }
}
