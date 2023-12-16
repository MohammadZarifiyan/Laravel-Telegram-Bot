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
			'message', 'edited_message', 'channel_post', 'edited_channel_post', 'chat_member_updated', 'chat_join_request' => $this->request->input($update_type.'.chat.type'),
            'inline_query' => $this->request->input($update_type.'.chat_type'),
            'callback_query' => $this->request->input($update_type.'.message.chat.type'),
			default => null
		};
	}
}