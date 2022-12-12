<?php

namespace MohammadZarifiyan\Telegram\Services;

use Exception;
use Illuminate\Http\Request;
use MohammadZarifiyan\Telegram\Interfaces\Command as CommandInterface;

class Command implements CommandInterface
{
	public string $signature;
	public ?string $value = null;

	public function __construct(public Request $request)
	{
		$this->authorizeRequest();

		$this->initialize();
	}
	
	private function authorizeRequest(): void
	{
		if (!$this->isCommand()) {
			throw new Exception('Specified request does not contain a Telegram command.');
		}
	}
	
	private function isCommand(): bool
	{
		return $this->request
			->collect('message.entities.*.type')
			->contains('bot_command');
	}
	
	private function initialize(): void
	{
		$command_parts = explode(' ', $this->request->input('message.text'));
		
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
