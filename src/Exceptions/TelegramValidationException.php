<?php

namespace MohammadZarifiyan\Telegram\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TelegramValidationException extends Exception
{
	/**
	 * Create a new exception instance.
	 *
	 * @param \Illuminate\Contracts\Validation\Validator $validator
	 */
	public function __construct(public $validator, string $message = "", int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

	public function errors()
	{
		return $this->validator
			->errors()
			->messages();
	}

	public function render()
	{
		return response(status: Response::HTTP_OK);
	}
}
