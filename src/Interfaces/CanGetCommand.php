<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

use MohammadZarifiyan\Telegram\Interfaces\Command as CommandInterface;
use MohammadZarifiyan\Telegram\Update;

interface CanGetCommand
{
	public function getCommand(Update $update): ?CommandInterface;
}
