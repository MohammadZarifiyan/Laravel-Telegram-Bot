<?php

namespace MohammadZarifiyan\Telegram;

class Attachment
{
    public function __construct(
        public readonly mixed $content,
        public readonly string $filename,
        public readonly array $headers = []
    ) {
        //
    }
}
