<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

use MohammadZarifiyan\Telegram\Update;

interface GainerResolver
{
	public function __invoke(Update $update);
}
