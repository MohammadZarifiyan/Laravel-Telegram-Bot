<?php

namespace MohammadZarifiyan\Telegram;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use MohammadZarifiyan\Telegram\Exceptions\TelegramNotificationException;
use MohammadZarifiyan\Telegram\Facades\Telegram;
use MohammadZarifiyan\Telegram\Interfaces\PendingRequestStack as PendingRequestStackInterface;
use MohammadZarifiyan\Telegram\Interfaces\PendingRequestBuilder as PendingRequestBuilderInterface;
use Throwable;

class Channel
{
	public function send($notifiable, Notification $notification): void
	{
        $responses = Telegram::concurrent(function (PendingRequestStackInterface $pendingRequestStack) use ($notifiable, $notification) {
            $routeNotification = $notifiable->routeNotificationFor('telegram', $notification);
            $routes = new Collection($routeNotification);

            foreach ($routes as $route) {
                $recipient = $this->getRecipientFromRoute($route);
                $content = $notification->toTelegram($notifiable, $recipient);

                if ($content instanceof TelegramRequestContent) {
                    $pendingRequestStack->add()
                        ->when(
                            $route instanceof TelegramRequestOptions,
                            fn (PendingRequestBuilderInterface $pendingRequestBuilder) => $pendingRequestBuilder->setApiKey($route->apiKey)->setEndpoint($route->endpoint)
                        )
                        ->setMethod($content->method)
                        ->setData($content->data)
                        ->setReplyMarkup($content->replyMarkup);
                }
                else {
                    throw new Exception('toTelegram method must return a TelegramRequestContent');
                }
            }
        });

        $exceptions = array_reduce(
            $responses,
            function ($carry, $response) {
                if ($response instanceof Throwable) {
                    $carry[] = $response;

                    return $carry;
                }

                if ($response instanceof Response) {
                    $exception = $response->toException();

                    if (!is_null($exception)) {
                        $carry[] = $response;
                    }
                }

                return $carry;
            },
            []
        );

        if (count($exceptions) > 0) {
            throw new TelegramNotificationException($exceptions);
        }
	}

    public function getRecipientFromRoute($route): string|int
    {
        return match (true) {
            $route instanceof TelegramRequestOptions => $route->recipient,
            is_string($route), is_int($route) => $route,
            default => throw new Exception('Invalid recipient'),
        };
    }
}
