<?php

namespace MohammadZarifiyan\Telegram\Abstractions;

use MohammadZarifiyan\Telegram\Attachment;
use MohammadZarifiyan\Telegram\Interfaces\PendingRequest as PendingRequestInterface;

abstract class PendingRequest implements PendingRequestInterface
{
    public function getBody(): array
    {
        return array_filter(
            $this->getContent(),
            fn ($value) => $value instanceof Attachment === false
        );
    }

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