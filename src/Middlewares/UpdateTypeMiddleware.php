<?php

namespace MohammadZarifiyan\Telegram\Middlewares;

use Closure;
use Illuminate\Http\Request;

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
        if ($request->hasAny($updateTypes)) {
            return $next($request);
        }

        return false;
    }
}
