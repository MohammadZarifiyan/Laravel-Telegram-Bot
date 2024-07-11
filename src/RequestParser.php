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
}