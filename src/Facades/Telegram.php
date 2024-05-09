<?php

namespace MohammadZarifiyan\Telegram\Facades;

use Closure;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Facade;
use MohammadZarifiyan\Telegram\Interfaces\ReplyMarkup;
use MohammadZarifiyan\Telegram\Interfaces\Telegram as TelegramInterface;
use MohammadZarifiyan\Telegram\Update;

/**
 * @method static void handleRequest(Request $request)
 * @method static \MohammadZarifiyan\Telegram\Telegram setApiKey(string $apiKey)
 * @method static \MohammadZarifiyan\Telegram\Telegram setEndpoint(string $endpoint)
 * @method static Update|null getUpdate()
 * @method static int getBotId()
 * @method static Response perform(string $method, array $data = [], ReplyMarkup|string|null $replyMarkup = null)
 * @method static array<Response> concurrent(Closure $closure)
 * @method static string generateFileUrl(string $filePath)
 * @method static macro($name, $macro)
 * @method static mixin($mixin, $replace = true)
 * @method static hasMacro($name)
 * @method static flushMacros()
 *
 * @see \MohammadZarifiyan\Telegram\Telegram
 */

class Telegram extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return TelegramInterface::class;
    }

    public static function fresh(string $apiKey = null, string $endpoint = null): TelegramInterface
    {
        return App::makeWith(TelegramInterface::class, compact('apiKey', 'endpoint'));
    }
}
