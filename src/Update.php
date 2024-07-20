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
	protected ?string $updateType;
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

        $command_parts = $this->string('message.text')->explode(' ');
        $signature = substr($command_parts->first(), 1);
        $value = $command_parts->slice(1)->implode(' ');
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
