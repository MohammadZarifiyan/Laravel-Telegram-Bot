<?php

namespace MohammadZarifiyan\Telegram;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use MohammadZarifiyan\Telegram\Interfaces\Command as CommandInterface;
use MohammadZarifiyan\Telegram\Interfaces\GainerResolver;
use MohammadZarifiyan\Telegram\Interfaces\RequestParser;

class Update extends Request
{
	protected ?string $updateType;
	protected ?CommandInterface $command;
	protected mixed $gainer;
	protected RequestParser $requestParser;

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
	 * @return ?CommandInterface
	 */
	public function toCommand(): ?CommandInterface
	{
		if (isset($this->command)) {
			return $this->command;
		}

        if (!$this->isCommand()) {
            return $this->command = null;
        }

        $commandParts = $this->string('message.text')->explode(' ');
        $signature = substr($commandParts->first(), 1);
        $value = $commandParts->slice(1)->implode(' ');
        $value = trim($value) === '' ? null : trim($value);

        return $this->command = new Command($signature, $value);
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
	 * Get gainer resolver.
	 *
	 * @return null|GainerResolver
	 */
	public function getGainerResolver(): ?GainerResolver
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
		if (!isset($this->gainer)) {
            $resolver = $this->getGainerResolver();
            $this->gainer = $resolver instanceof GainerResolver ? call_user_func($resolver, $this) : null;
		}

        return $this->gainer;
	}
}
