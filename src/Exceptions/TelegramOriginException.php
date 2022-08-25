<?php

namespace MohammadZarifiyan\Telegram\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class TelegramOriginException extends Exception
{
	public function render()
	{
		return response(status: Response::HTTP_UNAUTHORIZED);
	}
}