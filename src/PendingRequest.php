<?php

namespace MohammadZarifiyan\Telegram;

use Illuminate\Support\Str;
use MohammadZarifiyan\Telegram\Interfaces\PendingRequest as PendingRequestInterface;
use MohammadZarifiyan\Telegram\Interfaces\ReplyMarkup;

class PendingRequest implements PendingRequestInterface
{
    public array $body;
    public array $attachments = [];

    public function __construct(
        protected string $endpoint,
        protected string $apiKey,
        public string $method,
        public array $data = [],
        public ReplyMarkup|string|null $replyMarkup = null
    ) {
        $this->setContents();
    }

    private function setContents(): void
    {
        $contents = array_merge(
            $this->data,
            $this->getReplyMarkup(),
        );

        $contents = array_filter(
            $contents,
            function ($value, $key) {
                if ($value instanceof Attachment) {
                    $this->attachments[] = [
                        $key,
                        $value->content,
                        $value->filename,
                        $value->headers
                    ];

                    return false;
                }

                return true;
            },
            ARRAY_FILTER_USE_BOTH
        );

        $this->body = array_map_recursive($contents, function ($value, $key, int $depth) {
            if ($depth > 0 && $value instanceof Attachment) {
                $uuid = Str::uuid()->toString();

                $this->attachments[] = [
                    $uuid,
                    $value->content,
                    $value->filename,
                    $value->headers
                ];

                return 'attach://'.$uuid;
            }

            return $value;
        });
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
        return $this->body;
    }

    /**
     * Get attachments like photos, videos, etc.
     *
     * @return array
     */
    public function getAttachments(): array
    {
        return $this->attachments;
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
            'reply_markup' => $resolved_reply_markup()
        ];
    }
}
