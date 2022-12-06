<?php

namespace MohammadZarifiyan\Telegram\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class TelegramMiddlewareFailedException extends TelegramException
{
	public function render()
	{
		return response(status: Response::HTTP_OK);
	}
}