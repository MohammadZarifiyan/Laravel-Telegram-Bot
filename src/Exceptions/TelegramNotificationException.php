<?php

namespace MohammadZarifiyan\Telegram\Exceptions;

class TelegramNotificationException extends TelegramException
{
    public function __construct(public array $exceptions)
    {
        parent::__construct('Several Telegram HTTP requests encountered errors.');
    }

    public function getExceptions(): array
    {
        return $this->exceptions;
    }

    public function report(): bool
    {
        foreach ($this->exceptions as $exception) {
            report($exception);
        }

        return true;
    }
}
