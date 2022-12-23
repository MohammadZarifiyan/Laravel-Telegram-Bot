<?php

namespace MohammadZarifiyan\Telegram\Traits;

use MohammadZarifiyan\Telegram\Casts\Serializable;

trait Gainer
{
    /**
     * Initializes trait
     *
     * @return void
     */
    public function initializeTelegramGainer(): void
    {
		$telegram_id_column_name = $this->getTelegramIdColumnName();
		$stage_column_name = $this->getStageColumnName();
		
        static::mergeFillable([
			$telegram_id_column_name,
			$stage_column_name
        ]);

        static::mergeCasts([
			$telegram_id_column_name => 'integer',
			$stage_column_name => Serializable::class
        ]);
    }
	
	/**
	 * Get the name of "telegram id" column.
	 *
	 * @return string
	 */
	public function getTelegramIdColumnName(): string
	{
		return defined(static::class.'::TELEGRAM_ID') ? static::TELEGRAM_ID : 'telegram_id';
	}
	
	/**
	 * Get the name of "stage" column.
	 *
	 * @return string
	 */
	public function getStageColumnName(): string
	{
		return defined(static::class.'::STAGE') ? static::STAGE : 'stage';
	}
	
	/**
	 * Get route notification for Telegram.
	 */
	public function routeNotificationForTelegram($notification): int
	{
		return $this->{$this->getTelegramIdColumnName()};
	}
}
