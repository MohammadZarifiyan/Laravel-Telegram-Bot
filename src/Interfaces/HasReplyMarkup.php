<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

interface HasReplyMarkup
{
	/**
	 * Returns Reply Markup class.
	 *
	 * @return ReplyMarkup|string|null
	 */
    public function replyMarkup(): ReplyMarkup|string|null;
}
