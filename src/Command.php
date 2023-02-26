<?php

namespace MohammadZarifiyan\Telegram;

use MohammadZarifiyan\Telegram\Interfaces\Command as CommandInterface;

class Command implements CommandInterface
{
	public function __construct(public string $signature, public mixed $value)
	{
		//
	}
	
	public function getSignature(): string
	{
		return $this->signature;
	}
	
	public function getValue(): mixed
	{
		return $this->value;
	}
}
