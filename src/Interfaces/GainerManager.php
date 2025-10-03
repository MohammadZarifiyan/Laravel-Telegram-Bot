<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

use MohammadZarifiyan\Telegram\Update;

interface GainerManager
{
    public function getCachedGainer(Update $update): mixed;
}
