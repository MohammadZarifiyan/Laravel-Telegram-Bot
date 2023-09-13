<?php

namespace MohammadZarifiyan\Telegram\Abstractions;

use MohammadZarifiyan\Telegram\Attachment;

abstract class PendingRequest
{
	/**
	 * Returns URL for pending request.
	 *
	 * @return string
	 */
	abstract public function getUrl(): string;
	
	/**
	 * Get request body.
	 *
	 * @return array
	 */
    public function getBody(): array
    {
        return array_filter(
            $this->getContent(),
            fn ($value) => $value instanceof Attachment === false
        );
    }
	
	/**
	 * Get request attachments
	 *
	 * @return array
	 */
    public function getAttachments(): array
    {
        $attachments = array_filter(
            $this->getContent(),
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

    abstract public function getContent(): array;
}