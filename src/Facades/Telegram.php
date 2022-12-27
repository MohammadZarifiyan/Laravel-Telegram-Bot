<?php

namespace MohammadZarifiyan\Telegram\Facades;

use Closure;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use MohammadZarifiyan\Telegram\Interfaces\Payload;
use MohammadZarifiyan\Telegram\Interfaces\Telegram as TelegramInterface;
use MohammadZarifiyan\Telegram\Update;

/**
 * @method static TelegramInterface fresh(string $apiKey)
 * @method static void handleRequest(Request $request)
 * @method static Update|null getUpdate()
 * @method static Response execute(Payload|string $payload, array $merge = [])
 * @method static array<Response> executeAsync(Closure $closure)
 * @method static string generateFileUrl(string $filePath)
 */

class Telegram extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return TelegramInterface::class;
    }
}
