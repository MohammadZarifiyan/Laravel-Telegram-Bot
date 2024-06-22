<?php

namespace MohammadZarifiyan\Telegram;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use MohammadZarifiyan\Telegram\Interfaces\Command as CommandInterface;
use MohammadZarifiyan\Telegram\Interfaces\GainerResolver;
use MohammadZarifiyan\Telegram\Interfaces\RequestParser;

class Update extends Request
{
	protected ?string $updateType, $chatType;
	protected CommandInterface $command;
	protected mixed $gainer;
	protected RequestParser $requestParser;
    private bool $initializedGainerResolver = false;
	
	protected function getRequestParser(): RequestParser
	{
		return $this->requestParser ??= App::makeWith(RequestParser::class, ['request' => $this]);
	}
	
	/**
	 * Checks if current Telegram update is caused by a Telegram bot command.
	 *
	 * @return bool
	 */
	public function isCommand(): bool
	{
		return $this->collect('message.entities')->some(fn (array $entity) => $entity['type'] === 'bot_command' && $entity['offset'] === 0);
	}
	
	/**
	 * Converts Telegram update to command instance.
	 *
	 * @return CommandInterface
	 */
	public function toCommand(): CommandInterface
	{
		if (isset($this->command)) {
			return $this->command;
		}
		
		$command_parts = explode(' ', $this->input('message.text'));
		$signature = substr($command_parts[0], 1);
		$trimmed_value = trim(
			implode(' ', array_slice($command_parts, 1))
		);
		return $this->command = new Command($signature, $trimmed_value);
	}
	
	/**
	 * Updates command.
	 *
	 * @param CommandInterface $command
	 * @return static
	 */
	public function setCommand(CommandInterface $command): static
	{
		$this->command = $command;
		
		return $this;
	}
	
	/**
	 * Returns type of the Telegram update.
	 *
	 * @return string|null
	 */
	public function type(): ?string
	{
		if (!isset($this->updateType)) {
			$this->updateType = $this->getRequestParser()->getUpdateType();
		}
		
		return $this->updateType;
	}
	
	/**
	 * Returns chat type of the Telegram update.
	 *
	 * @return string|null
	 */
	public function chatType(): ?string
	{
		if (!isset($this->chatType)) {
			$this->chatType = $this->getRequestParser()->getChatType();
		}
		
		return $this->chatType;
	}
	
	/**
	 * Get gainer resolver.
	 *
	 * @return callable|null
	 */
	public function getGainerResolver(): callable|null
	{
		return try_resolve(
			config('telegram.gainer-resolver')
		);
	}
	
	/**
	 * Returns gainer if already was set,
	 * otherwise sets gainer by gainer resolver.
	 *
	 * @return mixed
	 */
	public function gainer(): mixed
	{
		if (isset($this->gainer)) {
			return $this->gainer;
		}
		
		$resolver = $this->getGainerResolver();
		
		if ($resolver instanceof GainerResolver) {
            if ($this->initializedGainerResolver) {
                throw new Exception('You should not run gainer() inside the GainerResolver.');
            }

            $this->initializedGainerResolver = true;
            $this->gainer = $resolver($this);

            return $this->gainer;
		}
		
		return null;
	}
}
