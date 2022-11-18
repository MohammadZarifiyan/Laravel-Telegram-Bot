# Telegram

A Laravel package that helps to create Telegram Bots easily.


## Installation

1 - Include package inside your project.
```shell
composer require mohammad-zarifiyan/telegram
```

2 - Publish config file:
```shell
php artisan vendor:publish --provider="MohammadZarifiyan\Telegram\Providers\TelegramServiceProvider" --tag="telegram-config"
```

3 - Add these options to services.php:
```injectablephp
'telegram' => [
    'secure_token' => env('TELEGRAM_SECURE_TOKEN'), // Telegram secure token (Recommended to fill)
    'update-route' => env('TELEGRAM_UPDATE_ROUTE', 'telegram-update'), // Route name of Telegram update handler
    'api_key' => env('TELEGRAM_API_KEY') // API Key for Telegram bot authorization
]
```
**Notice: Only characters `A-Z`, `a-z`, `0-9`, `_` and `-` are allowed for `secure_token`.**

4 - Publish migrations:
```shell
php artisan vendor:publish --provider="MohammadZarifiyan\Telegram\Providers\TelegramServiceProvider" --tag="telegram-migrations"
```

5 - Publish kernel:
```bash
php artisan vendor:publish --provider="MohammadZarifiyan\Telegram\Providers\TelegramServiceProvider" --tag="telegram-kernel"
```

6 - Then set `TELEGRAM_API_KEY` in `.env` file to your token that taken from [@BotFather](https://t.me/BotFather).

7 - Then set `APP_URL` in `.env` file to your application URL.

**Notice: Your application URL must start with `https://` and you have to install a valid SSL/TSL certificate on it.**

8 - Declare a route named `telegram-update` to handle Telegram updates.

9 - Set webhook to receive Telegram updates when someone intracts with your bot.
```bash
php artisan bot:set-webhook
```
