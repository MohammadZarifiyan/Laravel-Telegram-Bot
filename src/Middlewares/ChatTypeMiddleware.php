<?php

namespace MohammadZarifiyan\Telegram\Middlewares;

use Closure;
use MohammadZarifiyan\Telegram\Update;
use Throwable;

class ChatTypeMiddleware
{
	/**
	 * Check Telegram update coming from valid type of chat.
	 *
	 * @param Update $request
	 * @param Closure $next
	 * @param string ...$chatTypes
	 * @return mixed
	 */
    public function handle(Update $request, Closure $next, string ...$chatTypes)
    {
        try {
			if (in_array($request->chatType(), $chatTypes)) {
				return $next($request);
			}
		}
		catch (Throwable) {
			//
		}

        return false;
    }
}
