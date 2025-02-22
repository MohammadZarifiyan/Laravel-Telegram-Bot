<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

interface Proxy
{
    public function getKey(): string;

    public function getConfiguration();
}
