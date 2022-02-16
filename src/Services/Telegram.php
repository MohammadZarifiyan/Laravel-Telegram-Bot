<?php

namespace MohammadZarifiyan\Telegram\Services;

use Exception;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use MohammadZarifiyan\Telegram\Interfaces\Response;
use MohammadZarifiyan\Telegram\Traits\ReplyMarkup;

class Telegram implements \MohammadZarifiyan\Telegram\Interfaces\Telegram
{
    public function __construct(public Request $request)
    {
        //
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function sendResponse(Response|string $response): ClientResponse
    {
        $resolved_response = is_string($response) ? app($response) :  $response;

        return Http::baseUrl($this->getBaseUrl())
            ->get(
                $resolved_response->method(),
                in_array(ReplyMarkup::class, class_uses_recursive($resolved_response))
                    ? $resolved_response->resolveWithReplayMarkup()
                    : $resolved_response->data()
            );
    }

    /**
     * Returns base URL for sending response using Telegram API.
     *
     * @return string
     * @throws Exception
     */
    private function getBaseUrl(): string
    {
        $api_key = config('telegram.api_key');

        if (!$api_key) {
            throw new Exception('Telegram API Key is not set.');
        }

        return sprintf('https://api.telegram.org/bot%s', $api_key);
    }

    /**
     * @inheritDoc
     */
    public function getUpdateType(): ?string
    {
        return collect($this::UPDATE_TYPES)
            ->intersect($this->request->keys())
            ->first();
    }

    /**
     * @inheritDoc
     */
    public function getChatType(): ?string
    {
        $update_type = $this->getUpdateType();

        return match($update_type) {
            'message', 'edited_message', 'my_chat_member', 'chat_member', 'chat_join_request' => $this->request->input(sprintf('%s.chat.type', $update_type)),
            'channel_post', 'edited_channel_post' => 'channel',
            'inline_query', 'chosen_inline_result', 'callback_query', 'shipping_query', 'pre_checkout_query', 'poll_answer' => 'private',
            'poll' => null,
        };
    }

    /**
     * @inheritDoc
     */
    public function getUser(): ?object
    {
        $update_type = $this->getUpdateType();

        $match = match($update_type) {
            'poll' => null,
            default => $this->request->input(sprintf('%s.from', $update_type)),
        };

        return $match ? (object) $match : null;
    }
}
