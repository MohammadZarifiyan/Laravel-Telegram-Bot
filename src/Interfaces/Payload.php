<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

interface Payload
{
	/**
	 * Telegram API method.
	 *
	 * @return string
	 */
    public function method(): string;
	
	/**
	 * Data to send to Telegram API.
	 *
	 * @return array
	 */
    public function data(): array;
}
