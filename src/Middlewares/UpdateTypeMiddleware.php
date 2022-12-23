<?php

namespace MohammadZarifiyan\Telegram\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use MohammadZarifiyan\Telegram\Interfaces\RequestParser;

class UpdateTypeMiddleware
{
    /**
     * Check the update is a specified Telegram update type or not.
     *
     * @param Request $request
     * @param Closure $next
     * @param string ...$updateTypes
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string ...$updateTypes)
    {
		$parser = App::makeWith(RequestParser::class, compact('request'));
		
        if (in_array($parser->getUpdateType(), $updateTypes)) {
            return $next($request);
        }

        return false;
    }
}
