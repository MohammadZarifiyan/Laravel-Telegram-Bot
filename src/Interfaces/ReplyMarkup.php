<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

interface ReplyMarkup
{
	/**
	 * Returns reply markup data.
	 *
	 * @return array
	 */
    public function __invoke(): array;
}
