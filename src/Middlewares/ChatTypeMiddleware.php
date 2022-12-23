<?php

namespace MohammadZarifiyan\Telegram\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use MohammadZarifiyan\Telegram\Interfaces\RequestParser;

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
		$parser = App::makeWith(RequestParser::class, compact('request'));

		if (in_array($parser->getChatType(), $chatTypes)) {
			return $next($request);
		}

        return false;
    }
}
