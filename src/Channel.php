<?php

namespace MohammadZarifiyan\Telegram;

use Exception;
use Illuminate\Notifications\Notification;
use MohammadZarifiyan\Telegram\Facades\Telegram;
use MohammadZarifiyan\Telegram\Interfaces\Payload;
use MohammadZarifiyan\Telegram\Interfaces\TelegramRequestContent;

class Channel
{
	public function send($notifiable, Notification $notification): void
	{
		$recipient = $notifiable->routeNotificationFor('telegram', $notification);
		$content = $notification->toTelegram($notifiable);

        if (is_string($content) || $content instanceof Payload) {
            Telegram::execute($content, ['chat_id' => $recipient]);
        }
        else if ($content instanceof TelegramRequestContent) {
            Telegram::perform(
                $content->method,
                array_merge($content->data, ['chat_id' => $recipient]),
                $content->replyMarkup
            );
        }
        else {
            throw new Exception('toTelegram method must return a Payload or TelegramRequestContent');
        }
	}
}
