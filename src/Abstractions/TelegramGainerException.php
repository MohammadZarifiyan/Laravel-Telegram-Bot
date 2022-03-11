<?php

namespace MohammadZarifiyan\Telegram\Abstractions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

abstract class TelegramGainerException extends Exception
{
    public function render()
    {
        return http_response_code(Response::HTTP_NO_CONTENT);
    }

    abstract public function report();
}
