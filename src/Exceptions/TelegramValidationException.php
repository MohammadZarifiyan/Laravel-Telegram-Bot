<?php

namespace MohammadZarifiyan\Telegram\Exceptions;

use Illuminate\Validation\ValidationException;

class TelegramValidationException extends ValidationException
{
	/**
	 * The status code to use for the response.
	 *
	 * @var int
	 */
	public $status = 200;

	public function __construct($validator, $response = null, $errorBag = 'default')
	{
		parent::__construct($validator, $response, $errorBag);
	}
}
