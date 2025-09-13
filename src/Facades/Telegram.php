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
use RuntimeException;
use SodiumException;

/**
 * @method static void handleRequest(Request $request)
 * @method static TelegramInterface setApiKey(?string $apiKey = null)
 * @method static TelegramInterface setEndpoint(?string $endpoint = null)
 * @method static TelegramInterface setSecretToken(?string $secretToken = null)
 * @method static Update|null getUpdate()
 * @method static int getBotId()
 * @method static Response perform(string $method, array $data = [], ReplyMarkup|string|null $replyMarkup = null)
 * @method static array<Response> concurrent(Closure $closure)
 * @method static bool validateAuthorizationData(array $authData)
 * @method static bool validateWebAppInitData(string $initData)
 * @method static string generateFileUrl(string $filePath)
 * @method static macro($name, $macro)
 * @method static mixin($mixin, $replace = true)
 * @method static hasMacro($name)
 * @method static flushMacros()
 *
 * @see \MohammadZarifiyan\Telegram\TelegramManager
 */

class Telegram extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return TelegramInterface::class;
    }

    public static function fresh(string $apiKey = null, string $endpoint = null, ?string $secretToken = null): TelegramInterface
    {
        return App::makeWith(TelegramInterface::class, compact('apiKey', 'endpoint', 'secretToken'));
    }

    public static function validateWebAppSignature(array $data, int $botId): bool
    {
        if (!isset($data['signature']) || !is_string($data['signature'])) {
            return false;
        }

        $signatureBinary = hex2bin($data['signature']);

        if ($signatureBinary === false) {
            return false;
        }

        $dataCheckString = collect($data)
            ->except(['hash', 'signature'])
            ->sortKeys()
            ->map(fn ($key, $value) => $key . '=' . $value)
            ->implode(PHP_EOL);

        $message = $botId . ':WebAppData' . PHP_EOL . $dataCheckString;

        $publicKeyHex = config('services.telegram.public-key');

        if (!is_string($publicKeyHex)) {
            throw new RuntimeException('Telegram public key HEX is not properly configured.');
        }

        $publicKeyBinary = hex2bin($publicKeyHex);

        if ($publicKeyBinary === false) {
            throw new RuntimeException('Telegram public key HEX is invalid.');
        }

        try {
            return sodium_crypto_sign_verify_detached($signatureBinary, $message, $publicKeyBinary);
        }
        catch (SodiumException) {
            return false;
        }
    }
}
