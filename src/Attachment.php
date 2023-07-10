<?php

namespace MohammadZarifiyan\Telegram;

class Attachment
{
    public function __construct(
        public string $name,
        public $contents,
        public string $filename,
        public array $headers = []
    ) {
        //
    }
}
