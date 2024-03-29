<?php

namespace MohammadZarifiyan\Telegram\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class TelegramOriginException extends TelegramException
{
	public function render()
	{
		return response(status: Response::HTTP_UNAUTHORIZED);
	}
}