<?php

namespace MohammadZarifiyan\Telegram;

class Command
{
	public function __construct(
        public readonly string $signature,
        public readonly ?string $value
    ) {
		//
	}
}
