<?php

namespace MohammadZarifiyan\Telegram;

use MohammadZarifiyan\Telegram\Interfaces\Command as CommandInterface;

class Command implements CommandInterface
{
	private string $signature;
	private ?string $value = null;

	public function __construct(string $text)
	{
		$this->initialize($text);
	}
	
	private function initialize(string $text): void
	{
		$command_parts = explode(' ', $text);
		
		$this->signature = substr($command_parts[0], 1);
		
		$trimmed_value = trim(
			implode(' ', array_slice($command_parts, 1))
		);
		
		$this->value = $trimmed_value ?? null;
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
