<?php

namespace MohammadZarifiyan\Telegram;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use MohammadZarifiyan\Telegram\Interfaces\Command as CommandInterface;
use MohammadZarifiyan\Telegram\Interfaces\GainerResolver;
use MohammadZarifiyan\Telegram\Interfaces\RequestParser;

class Update extends Request
{
	protected RequestParser $requestParser;
    protected ?GainerResolver $gainerResolver = null;

    public static function createFrom(Request|Update $from, $to = null): static
    {
        /**
         * @var Update $update
         */
        $update = parent::createFrom($from, $to);

        if ($from instanceof Update) {
            $update->setGainerResolver($from->getGainerResolver());
        }

        return $update;
    }

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
     * Set the gainer resolver class.
     *
     * @param GainerResolver|null $resolver
     * @return $this
     */
    public function setGainerResolver(?GainerResolver $resolver): static
    {
        $this->gainerResolver = $resolver;

        return $this;
    }
	
	/**
	 * Get gainer resolver.
	 *
	 * @return null|GainerResolver
	 */
	public function getGainerResolver(): ?GainerResolver
	{
		return $this->gainerResolver ?? null;
	}
	
	/**
	 * Get the gainer making the update.
	 *
	 * @return mixed
	 */
	public function gainer(): mixed
	{
        $resolver = $this->getGainerResolver();

        return $resolver instanceof GainerResolver ? call_user_func($resolver, $this) : null;
	}
}
