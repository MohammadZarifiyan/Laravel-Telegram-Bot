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
		$telegram_id_column_name = $this->getTelegramIdColumnName();
		$handler_column_name = $this->getHandlerColumnName();
		
        static::mergeFillable([
			$telegram_id_column_name,
			$handler_column_name
        ]);

        static::mergeCasts([
			$telegram_id_column_name => 'integer',
			$handler_column_name => Serializable::class
        ]);
    }
	
	public function getTelegramIdColumnName(): string
	{
		return 'telegram_id';
	}
	
	public function getHandlerColumnName(): string
	{
		return 'handler';
	}
}
