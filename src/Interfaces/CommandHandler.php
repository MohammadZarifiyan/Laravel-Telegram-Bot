<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

use MohammadZarifiyan\Telegram\Update;

interface CommandHandler
{
	/**
	 * The signature(s) of the Telegram bot command that can be handled by current CommandHandler.
	 *
	 * @param Update $update
	 * @return string|array
	 */
	public function getSignature(Update $update): string|array;

    /**
     * Handles the Telegram command.
	 *
	 * @param Update $update
     */
    public function handle(Update $update);
}
