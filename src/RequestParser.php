<?php

namespace MohammadZarifiyan\Telegram;

use Illuminate\Http\Request;
use MohammadZarifiyan\Telegram\Providers\TelegramServiceProvider;

class RequestParser implements Interfaces\RequestParser
{
	public ?string $updateType;
	
	public function __construct(public Request $request)
	{
		//
	}
	
	public function getUpdateType(): ?string
	{
		return $this->updateType ??= collect(TelegramServiceProvider::UPDATE_TYPES)
			->intersect($this->request->keys())
			->first();
	}
	
	public function getChatType(): ?string
	{
		$update_type = $this->getUpdateType();
		
		return match($update_type) {
			'message', 'edited_message', 'my_chat_member', 'chat_member', 'chat_join_request' => $this->request->input($update_type.'.chat.type'),
			'channel_post', 'edited_channel_post' => 'channel',
			'inline_query', 'chosen_inline_result', 'callback_query', 'shipping_query', 'pre_checkout_query', 'poll_answer' => 'private',
			default => null
		};
	}
}