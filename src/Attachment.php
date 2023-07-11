<?php

namespace MohammadZarifiyan\Telegram;

class Attachment
{
    public function __construct(
        public $contents,
        public string $filename,
        public array $headers = []
    ) {
        //
    }
}
