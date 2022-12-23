<?php

namespace MohammadZarifiyan\Telegram;

use Illuminate\Http\Request;
use MohammadZarifiyan\Telegram\Interfaces\Command as CommandInterface;
use MohammadZarifiyan\Telegram\Interfaces\GainerResolver;
use MohammadZarifiyan\Telegram\Providers\TelegramServiceProvider;

class Update extends Request
{
	protected ?string $updateType, $chatType;
	protected CommandInterface $command;
	protected mixed $gainer;
	
	/**
	 * Checks if current Telegram update is caused by a Telegram bot command.
	 *
	 * @return bool
	 */
	public function isCommand(): bool
	{
		return $this->collect('message.entities.*.type')->contains('bot_command');
	}
	
	/**
	 * Converts Telegram update to command instance.
	 *
	 * @return CommandInterface
	 */
	public function toCommand(): CommandInterface
	{
		return $this->command ??= new Command(
			$this->input('message.text')
		);
	}
	
	/**
	 * Returns type of the Telegram update.
	 *
	 * @return string|null
	 */
	public function type(): ?string
	{
		if (isset($this->updateType)) {
			return $this->updateType;
		}
		
		return $this->updateType = collect(TelegramServiceProvider::UPDATE_TYPES)
			->intersect($this->keys())
			->first();
	}
	
	/**
	 * Returns chat type of the Telegram update.
	 *
	 * @return string|null
	 */
	public function chatType(): ?string
	{
		if (isset($this->chatType)) {
			return $this->chatType;
		}
		
		$update_type = $this->type();
		
		return $this->chatType = match($update_type) {
			'message', 'edited_message', 'my_chat_member', 'chat_member', 'chat_join_request' => $this->input($update_type.'.chat.type'),
			'channel_post', 'edited_channel_post' => 'channel',
			'inline_query', 'chosen_inline_result', 'callback_query', 'shipping_query', 'pre_checkout_query', 'poll_answer' => 'private',
			default => null
		};
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
			return $this->gainer = call_user_func($resolver, $this);
		}
		
		return null;
	}
}
