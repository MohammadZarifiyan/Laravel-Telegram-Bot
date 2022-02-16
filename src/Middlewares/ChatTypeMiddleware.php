<?php

namespace MohammadZarifiyan\Telegram\Middlewares;

use Closure;
use Illuminate\Http\Request;
use MohammadZarifiyan\Telegram\Facades\Telegram;

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
        if (in_array(Telegram::getChatType(), $chatTypes)) {
            return $next($request);
        }

        return false;
    }
}
