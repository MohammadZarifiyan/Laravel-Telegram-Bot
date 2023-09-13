<?php

namespace MohammadZarifiyan\Telegram\Facades;

use Closure;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use MohammadZarifiyan\Telegram\Interfaces\Payload;
use MohammadZarifiyan\Telegram\Interfaces\ReplyMarkup;
use MohammadZarifiyan\Telegram\Update;

/**
 * @method static \MohammadZarifiyan\Telegram\Telegram fresh(string $apiKey = null, string $endpoint = null)
 * @method static void handleRequest(Request $request)
 * @method static \MohammadZarifiyan\Telegram\Telegram setApiKey(string $apiKey)
 * @method static Update|null getUpdate()
 * @method static Response execute(Payload|string $payload, array $merge = [])
 * @method static Response perform(string $method, array $data = [], ReplyMarkup|string|null $replyMarkup = null)
 * @method static array<Response> async(Closure $closure)
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
        return 'telegram';
    }
}
