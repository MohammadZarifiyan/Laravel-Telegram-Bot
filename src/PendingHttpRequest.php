<?php

namespace MohammadZarifiyan\Telegram;

use Illuminate\Support\Str;
use MohammadZarifiyan\Telegram\Interfaces\PendingHttpRequest as PendingHttpRequestInterface;

class PendingHttpRequest implements PendingHttpRequestInterface
{
    protected array $body;
    protected array $attachments = [];

    public function __construct(protected PendingTelegramRequest $pendingTelegramRequest)
    {
        $this->setContents();
    }

    private function setContents(): void
    {
        $contents = array_merge(
            $this->pendingTelegramRequest->data,
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
        return sprintf('%s/bot%s/%s', $this->pendingTelegramRequest->endpoint, $this->pendingTelegramRequest->apiKey, $this->pendingTelegramRequest->method);
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
    protected function getReplyMarkup(): array
    {
        $resolvedReplyMarkup = try_resolve($this->pendingTelegramRequest->replyMarkup);

        if (empty($resolvedReplyMarkup)) {
            return [];
        }

        return [
            'reply_markup' => $resolvedReplyMarkup()
        ];
    }
}
