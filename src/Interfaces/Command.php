<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

interface Command
{
	/**
	 * Returns command signature based on request.
	 *
	 * @return string
	 */
	public function getSignature(): string;
	
	/**
	 * Returns command value.
	 *
	 * @return null|string
	 */
	public function getValue(): ?string;
}
