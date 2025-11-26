<?php

namespace MohammadZarifiyan\Telegram\FakerProviders;

use Faker\Provider\Base;
use Illuminate\Support\Collection;
use MohammadZarifiyan\Telegram\TelegramBotApiKey;

class TelegramBotApiKeyProvider extends Base
{
    public function telegramBotApiKey(): TelegramBotApiKey
    {
        $id = rand(10000, 18446744073709551615);
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-';
        $lastAlphabetIndex = strlen($alphabet) - 1;
        $tokenHash = Collection::times(35)
            ->map(fn () => $alphabet[random_int(0, $lastAlphabetIndex)])
            ->join('');

        return new TelegramBotApiKey($id, $tokenHash);
    }
}
