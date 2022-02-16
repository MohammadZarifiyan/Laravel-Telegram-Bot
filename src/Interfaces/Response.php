<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

interface Response
{
    /**
     * Telegram API method.
     *
     * @return string
     */
    public function method(): string;

    /**
     * Data to use for sending response using Telegram API.
     *
     * @return array
     */
    public function data(): array;
}
