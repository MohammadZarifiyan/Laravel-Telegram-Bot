<?php

namespace MohammadZarifiyan\Telegram\Services;

use Illuminate\Http\Request;
use MohammadZarifiyan\Telegram\Interfaces\Command as CommandInterface;
use MohammadZarifiyan\Telegram\Providers\TelegramServiceProvider;

class TelegramUpdate extends Request
{
	protected string $updateType, $chatType;
	protected CommandInterface $command;
	protected ?object $from;
	
	/**
	 * Converts Telegram update to command instance.
	 *
	 * @return CommandInterface
	 */
	public function toCommand(): CommandInterface
	{
		return $this->command ??= new Command($this);
	}
	
	/**
	 * Returns type of the Telegram update.
	 *
	 * @return string
	 */
	public function getType(): string
	{
		return $this->updateType ??= collect(TelegramServiceProvider::UPDATE_TYPES)
			->intersect($this->keys())
			->first();
	}
	
	/**
	 * Returns chat type of the Telegram update.
	 *
	 * @return string
	 */
	public function getChatType(): string
	{
		if (!isset($this->chatType)) {
			$update_type = $this->getType();
			
			$this->chatType = match($update_type) {
				'message', 'edited_message', 'my_chat_member', 'chat_member', 'chat_join_request' => $this->input($update_type.'.chat.type'),
				'channel_post', 'edited_channel_post' => 'channel',
				'inline_query', 'chosen_inline_result', 'callback_query', 'shipping_query', 'pre_checkout_query', 'poll_answer' => 'private',
				default => null
			};
		}
		
		return $this->chatType;
	}
	
	/**
	 * Returns user that caused the Telegram update.
	 *
	 * @return object|null
	 */
	public function from(): ?object
	{
		if (!isset($this->from)) {
			$update_type = $this->getUpdateType();
			
			$from = match($update_type) {
				'poll' => null,
				default => $this->input($update_type.'.from')
			};
			
			$this->from = empty($from) ? null : (object) $from;
		}
		
		return $this->from;
	}
}
