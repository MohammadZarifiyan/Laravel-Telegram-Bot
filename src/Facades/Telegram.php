<?php

namespace MohammadZarifiyan\Telegram\Facades;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Facade;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static setApiKey(string $token)
 * @method static sendResponse(string|\MohammadZarifiyan\Telegram\Interfaces\Response $response)
 * @method static sendAsyncResponses(array $responses)
 * @method static getUpdateType
 * @method static getChatType
 * @method static getUser
 * @method static setGainer(Model $gainer)
 * @method static getGainer
 * @method static isCommand
 * @method static commandSignature
 * @method static generateFileUrl(string $filePath)
 */

class Telegram extends Facade
{
    public static function getFacadeAccessor()
    {
        return \MohammadZarifiyan\Telegram\Interfaces\Telegram::class;
    }

    public static function handleUpdate(Request $request)
    {
        return App::make(\MohammadZarifiyan\Telegram\Abstractions\Kernel::class)->handleUpdate($request);
    }
}
