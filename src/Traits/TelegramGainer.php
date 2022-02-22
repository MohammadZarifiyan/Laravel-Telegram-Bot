<?php

namespace MohammadZarifiyan\Telegram\Traits;

use MohammadZarifiyan\Telegram\Casts\Serializable;

trait TelegramGainer
{
    /**
     * Initializes trait
     *
     * @return void
     */
    public function initializeTelegramGainer()
    {
        static::mergeFillable([
            'telegram_id',
            'handler'
        ]);

        static::mergeCasts([
            'telegram_id' => 'integer',
            'handler' => Serializable::class
        ]);
    }
}
