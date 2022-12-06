<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface HasReplyMarkup
{
	/**
	 * Returns Reply Markup class.
	 *
	 * @param Request $request
	 * @param Model|null $gainer
	 * @return ReplyMarkup|string|null
	 */
    public function replyMarkup(Request $request, Model $gainer = null): ReplyMarkup|string|null;
}
