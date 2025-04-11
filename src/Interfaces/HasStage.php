<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

interface HasStage
{
	/**
	 * Get the name of "stage" column.
	 *
	 * @return string
	 */
	public function getStageColumnName(): string;
}
