<?php

namespace MohammadZarifiyan\Telegram;

use Exception;
use Illuminate\Notifications\Notification;
use MohammadZarifiyan\Telegram\Facades\Telegram;

class Channel
{
	public function send($notifiable, Notification $notification): void
	{
        $route = $notifiable->routeNotificationFor('telegram', $notification);

        if (is_null($route)) {
            return;
        }

        $content = $notification->toTelegram($notifiable);

        if ($content instanceof TelegramRequestContent === false) {
            throw new Exception('toTelegram method must return a TelegramRequestContent');
        }

        $telegram = Telegram::fresh();

        if ($route instanceof TelegramRequestOptions) {
            $telegram->setApiKey($route->apiKey);
            $telegram->setEndpoint($route->endpoint);
        }

        $response = $telegram->perform($content->method, $content->data, $content->replyMarkup);
        $response->throw();
	}
}
