<?php

namespace MohammadZarifiyan\Telegram\Traits;

trait HasReplyMarkup
{
    /**
     * Resolves data with reply_markup attribute if possible.
     */
    public function resolveWithReplayMarkup(): array
    {
		$replay_markup = static::replyMarkup();
		
		$resolved_reply_markup = try_resolve($replay_markup);
	
		$data = static::data();

		if (!$resolved_reply_markup) {
			return $data;
		}

        return array_merge(
            $data,
            ['reply_markup' => json_encode($resolved_reply_markup())]
        );
    }

	/**
	 * Returns Reply Markup class.
	 *
	 * @return \MohammadZarifiyan\Telegram\Interfaces\ReplyMarkup|string|null
	 */
    abstract public function replyMarkup(): \MohammadZarifiyan\Telegram\Interfaces\ReplyMarkup|string|null;
}
