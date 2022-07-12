<?php

namespace MohammadZarifiyan\Telegram\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TelegramAuthorizationException extends Exception
{
	/**
	 * The response from the gate.
	 *
	 * @var \Illuminate\Auth\Access\Response
	 */
	protected $response;
	
	/**
	 * Create a new authorization exception instance.
	 *
	 * @param  string|null  $message
	 * @param  mixed  $code
	 * @param  \Throwable|null  $previous
	 * @return void
	 */
	public function __construct($message = null, $code = null, Throwable $previous = null)
	{
		parent::__construct($message ?? 'This action is unauthorized.', 0, $previous);
		
		$this->code = $code ?: 0;
	}
	
	/**
	 * Get the response from the gate.
	 *
	 * @return \Illuminate\Auth\Access\Response
	 */
	public function response()
	{
		return $this->response;
	}
	
	/**
	 * Set the response from the gate.
	 *
	 * @param  \Illuminate\Auth\Access\Response  $response
	 * @return $this
	 */
	public function setResponse($response)
	{
		$this->response = $response;
		
		return $this;
	}
	
	/**
	 * Create a deny response object from this exception.
	 *
	 * @return \Illuminate\Auth\Access\Response
	 */
	public function toResponse()
	{
		return Response::deny($this->message, $this->code);
	}
	
	public function render()
	{
		return response(status: Response::HTTP_OK);
	}
}
