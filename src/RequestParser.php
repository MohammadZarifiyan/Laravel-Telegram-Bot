<?php

namespace MohammadZarifiyan\Telegram;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class RequestParser implements Interfaces\RequestParser
{
	public ?string $updateType;
	
	public function __construct(public Request $request)
	{
		//
	}
	
	public function getUpdateType(): ?string
	{
        if (!isset($this->updateType)) {
            $update_keys = new Collection($this->request->keys());

            $this->updateType = $update_keys->first(fn ($key) => $key !== 'update_id');
        }

        return $this->updateType;
    }
}