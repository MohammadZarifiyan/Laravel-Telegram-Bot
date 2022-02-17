<?php

namespace MohammadZarifiyan\Telegram\Traits;

use Symfony\Component\HttpFoundation\Response;

trait TelegramGainerException
{
    public function render()
    {
        return response(status: Response::HTTP_NO_CONTENT);
    }
}
