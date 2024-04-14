<?php

namespace MohammadZarifiyan\Telegram;

use MohammadZarifiyan\Telegram\Interfaces\PendingRequest as PendingRequestInterface;
use MohammadZarifiyan\Telegram\Interfaces\ReplyMarkup;

class PendingRequest implements PendingRequestInterface
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

    /**
     * Get URL of HTTP request.
     *
     * @return string
     */
	public function getUrl(): string
	{
		return sprintf('%s/bot%s/%s', $this->endpoint, $this->apiKey, $this->method);
	}

    /**
     * Get body of HTTP request.
     *
     * @return array
     */
    public function getBody(): array
    {
        return array_filter(
            $this->content,
            fn ($value) => $value instanceof Attachment === false
        );
    }

    /**
     * Get attachments like photos, videos, etc.
     *
     * @return array
     */
    public function getAttachments(): array
    {
        $attachments = array_filter(
            $this->content,
            fn ($value) => $value instanceof Attachment
        );

        return array_map(
            fn (Attachment $attachment, string $name) => [
                $name,
                $attachment->content,
                $attachment->filename,
                $attachment->headers
            ],
            $attachments,
            array_keys($attachments)
        );
    }

    /**
     * Get reply markup if exists.
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
