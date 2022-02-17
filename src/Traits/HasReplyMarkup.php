<?php

namespace MohammadZarifiyan\Telegram\Traits;

use Exception;

trait HasReplyMarkup
{
    /**
     * Returns data with reply_markup attribute
     *
     * @throws Exception
     */
    public function resolveWithReplayMarkup(): array
    {
        if (!method_exists($this, 'replyMarkup')) {
            throw new Exception('replyMarkup method is not implemented.');
        }

        $reply_markup = static::replyMarkup();

        return array_merge(
            static::data(),
            ['reply_markup' => json_encode($reply_markup())]
        );
    }

    /**
     * An array of replay_markup content
     *
     * @return \MohammadZarifiyan\Telegram\Interfaces\ReplyMarkup
     */
    abstract public function replyMarkup(): \MohammadZarifiyan\Telegram\Interfaces\ReplyMarkup;
}
