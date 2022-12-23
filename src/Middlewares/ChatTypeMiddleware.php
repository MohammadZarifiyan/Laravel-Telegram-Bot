<?php

namespace MohammadZarifiyan\Telegram\Middlewares;

use Closure;
use Illuminate\Http\Request;
use MohammadZarifiyan\Telegram\Update;
use Throwable;

class ChatTypeMiddleware
{
	/**
	 * Check Telegram update coming from valid type of chat.
	 *
	 * @param Request $request
	 * @param Closure $next
	 * @param string ...$chatTypes
	 * @return mixed
	 */
    public function handle(Request $request, Closure $next, string ...$chatTypes)
    {
        try {
			$update = Update::createFrom($request);
			
			if (in_array($update->chatType(), $chatTypes)) {
				return $next($request);
			}
		}
		catch (Throwable) {
			//
		}

        return false;
    }
}
