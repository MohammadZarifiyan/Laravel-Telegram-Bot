<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

use MohammadZarifiyan\Telegram\Update;

interface CommandHandler
{
	/**
	 * The signature(s) of the Telegram bot command.
	 *
	 * @return string|array
	 */
	public function getSignature(): string|array;

    /**
     * Handles the Telegram command.
	 *
	 * @param Update $update
     */
    public function handle(Update $update);
}
