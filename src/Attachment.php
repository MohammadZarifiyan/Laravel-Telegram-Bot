<?php

namespace MohammadZarifiyan\Telegram;

class Attachment
{
    public function __construct(
        public $content,
        public string $filename,
        public array $headers = []
    ) {
        //
    }
}
