<?php

namespace MohammadZarifiyan\Telegram;

use MohammadZarifiyan\Telegram\Interfaces\Command as CommandInterface;

class Command implements CommandInterface
{
	public function __construct(public string $signature, public ?string $value)
	{
		//
	}
	
	public function getSignature(): string
	{
		return $this->signature;
	}
	
	public function getValue(): ?string
	{
		return $this->value;
	}
}
