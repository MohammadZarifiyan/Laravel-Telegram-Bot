<?php

return [
    /**
     * Route name of Telegram update handler
     */
    'update-route' => env('TELEGRAM_UPDATE_ROUTE', 'telegram-update'),

    /**
     * Credentials
     */
    'api_key' => env('TELEGRAM_API_KEY'),

    /**
     * Classes
     */
    'service' => \MohammadZarifiyan\Telegram\Services\Telegram::class,
    'kernel' => \MohammadZarifiyan\Telegram\Kernel::class,
];
