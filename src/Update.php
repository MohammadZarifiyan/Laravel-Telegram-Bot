<?php

namespace MohammadZarifiyan\Telegram;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use MohammadZarifiyan\Telegram\Interfaces\GainerManager as GainerManagerInterface;
use MohammadZarifiyan\Telegram\Interfaces\RequestParser;

class Update extends Request
{
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
	 * @return ?Command
	 */
	public function toCommand(): ?Command
	{
        if (!$this->isCommand()) {
            return null;
        }

        $commandParts = $this->string('message.text')->explode(' ');
        $signature = substr($commandParts->first(), 1);
        $value = $commandParts->slice(1)->implode(' ');
        $value = trim($value) === '' ? null : trim($value);

        return new Command($signature, $value);
	}

	/**
	 * Returns type of the Telegram update.
	 *
	 * @return string|null
	 */
	public function type(): ?string
	{
        return $this->getRequestParser()->getUpdateType();
	}
	
	/**
	 * Get the gainer making the update.
	 *
	 * @return mixed
	 */
	public function gainer(): mixed
	{
        /**
         * @var GainerManagerInterface $gainerManager
         */
        $gainerManager = App::make(GainerManagerInterface::class);

        return $gainerManager->getCachedGainer($this);
	}
}
