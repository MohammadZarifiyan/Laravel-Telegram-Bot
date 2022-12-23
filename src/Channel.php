<?php

namespace MohammadZarifiyan\Telegram;

use Illuminate\Notifications\Notification;
use MohammadZarifiyan\Telegram\Facades\Telegram;

class Channel
{
	public function send($notifiable, Notification $notification): void
	{
		$recipient = $notifiable->routeNotificationFor('telegram', $notification);
		
		Telegram::execute(
			$notification->toTelegram($notifiable),
			['chat_id' => $recipient]
		);
	}
}
