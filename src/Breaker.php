<?php

namespace MohammadZarifiyan\Telegram;

class Breaker
{
	/**
	 * Continue processing update.
	 *
	 * @return bool
	 */
	public function continue(): bool
	{
		return false;
	}
	
	/**
	 * Stop processing update.
	 *
	 * @return bool
	 */
	public function stop(): bool
	{
		return true;
	}
}
