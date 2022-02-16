# Telegram

A Laravel package that helps to create Telegram Bots easily.


## Installation

1 - Include package inside your project.

```bash
composer require mohammad-zarifiyan/telegram
```

2 - Then set `TELEGRAM_API_KEY` in `.env` file to your token that taken from [@BotFather](https://t.me/BotFather).

3 - Then set `APP_URL` in `.env` file to your application URL.

**Note: application URL must start with `https://` and you have to install a valid SSL/TSL certificate on it.**

4 - Declare a route named `update-route` to handle Telegram updates.

5 - Set webhook to receive Telegram updates when someone intracts with your bot.
```bash
php artisan bot:set-webhook
```
