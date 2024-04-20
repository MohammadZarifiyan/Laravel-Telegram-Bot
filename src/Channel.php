<?php

namespace MohammadZarifiyan\Telegram;

use Exception;
use Illuminate\Notifications\Notification;
use MohammadZarifiyan\Telegram\Facades\Telegram;
use MohammadZarifiyan\Telegram\Interfaces\TelegramRequestContent;

class Channel
{
	public function send($notifiable, Notification $notification): void
	{
		$recipient = $notifiable->routeNotificationFor('telegram', $notification);
		$content = $notification->toTelegram($notifiable);

        if ($content instanceof TelegramRequestContent) {
            $response = Telegram::perform(
                $content->method,
                array_merge($content->data, ['chat_id' => $recipient]),
                $content->replyMarkup
            );

            $response->throw();
        }
        else {
            throw new Exception('toTelegram method must return a TelegramRequestContent');
        }
	}
}
