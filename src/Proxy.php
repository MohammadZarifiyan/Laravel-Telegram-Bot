<?php

namespace MohammadZarifiyan\Telegram;

class Proxy
{
    public function __construct(
        public readonly mixed $configuration,
        public readonly mixed $key = null
    ) {
        //
    }
}
