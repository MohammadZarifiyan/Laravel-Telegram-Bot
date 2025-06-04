<?php

namespace MohammadZarifiyan\Telegram\Exceptions;

use MohammadZarifiyan\Telegram\Interfaces\Command;
use Symfony\Component\HttpFoundation\Response;

class TelegramCommandHandlerNotFoundException extends TelegramException
{
	public function __construct(public Command $command)
	{
		parent::__construct();
	}
	
	public function render()
	{
		return response(status: Response::HTTP_OK);
	}
	
	public function context(): array
    {
		return [
			'signature' => $this->command->getSignature(),
			'value' => $this->command->getValue()
		];
	}
}