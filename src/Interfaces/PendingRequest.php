<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

interface PendingRequest
{
    /**
     * Returns URL for pending request.
     *
     * @return string
     */
    public function getUrl(): string;

    /**
     * Get request body.
     *
     * @return array
     */
    public function getBody(): array;

    /**
     * Get request attachments
     *
     * @return array
     */
    public function getAttachments(): array;
}