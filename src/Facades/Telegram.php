<?php

namespace MohammadZarifiyan\Telegram\Facades;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Facade;

/**
 * @method static setApiKey(string $token)
 * @method static sendResponse(string|\MohammadZarifiyan\Telegram\Interfaces\Response $response)
 * @method static sendAsyncResponses(array $responses)
 * @method static getUpdateType
 * @method static getChatType
 * @method static getUser
 * @method static isCommand
 * @method static commandSignature
 * @method static handleUpdate(Request $request)
 */

class Telegram extends Facade
{
    public static function getFacadeAccessor()
    {
        return \MohammadZarifiyan\Telegram\Interfaces\Telegram::class;
    }
}
