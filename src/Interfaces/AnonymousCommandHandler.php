<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

use MohammadZarifiyan\Telegram\Enums\Signal;
use MohammadZarifiyan\Telegram\Update;

interface AnonymousCommandHandler
{
    /**
     * Checks whether the current CommandHandler can process the command.
     *
     * @param Update $update
     * @return bool
     */
	public function matchesSignature(Update $update): bool;

    /**
     * Handles the Telegram command.
	 *
	 * @param Update $update
     * @return Signal
     */
    public function handle(Update $update): Signal;
}
