<?php

namespace MohammadZarifiyan\Telegram\Facades;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;

/**
 * @method static sendResponse(string|\MohammadZarifiyan\Telegram\Interfaces\Response $response)
 * @method static getUpdateType
 * @method static getChatType
 * @method static getUser
 */

class Telegram extends Facade
{
    public static function getFacadeAccessor()
    {
        return \MohammadZarifiyan\Telegram\Interfaces\Telegram::class;
    }

    public static function handleUpdate(Request $request)
    {
        return app(\MohammadZarifiyan\Telegram\Kernel::class)->handleUpdate($request);
    }
}
