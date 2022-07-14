<?php

namespace MohammadZarifiyan\Telegram\Facades;

use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Facade;
use Illuminate\Database\Eloquent\Model;
use MohammadZarifiyan\Telegram\Abstractions\Kernel;

/**
 * @method static \MohammadZarifiyan\Telegram\Interfaces\Telegram setApiKey(string $token)
 * @method static Response sendResponse(string|\MohammadZarifiyan\Telegram\Interfaces\Response $response)
 * @method static array<Response> sendAsyncResponses(array $responses)
 * @method static null|string getUpdateType
 * @method static null|string getChatType
 * @method static null|object getUser
 * @method static \MohammadZarifiyan\Telegram\Interfaces\Telegram setGainer(Model $gainer)
 * @method static null|Model getGainer
 * @method static bool isCommand
 * @method static null|string commandSignature
 * @method static string generateFileUrl(string $filePath)
 */

class Telegram extends Facade
{
    public static function getFacadeAccessor()
    {
        return \MohammadZarifiyan\Telegram\Interfaces\Telegram::class;
    }

    public static function handleUpdate(Request $request)
    {
        return App::make(Kernel::class)->handleUpdate($request);
    }
}
