<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

use Illuminate\Http\Client\Response as ClientResponse;

interface Telegram
{
    /**
     * Valid Telegram update types.
     */
    const UPDATE_TYPES = [
        'message',
        'edited_message',
        'channel_post',
        'edited_channel_post',
        'inline_query',
        'chosen_inline_result',
        'callback_query',
        'shipping_query',
        'pre_checkout_query',
        'poll',
        'poll_answer',
        'my_chat_member',
        'chat_member',
        'chat_join_request',
    ];

    /**
     * Sends response using Telegram API.
     *
     * @param Response|string $response
     * @return ClientResponse
     */
    public function sendResponse(Response|string $response): ClientResponse;

    /**
     * Sends responses using Telegram API asynchronous.
     *
     * @param array<Response|string> $responses
     * @return array<ClientResponse>
     */
    public function sendAsyncResponses(array $responses): array;

    /**
     * Returns type of incoming Telegram update.
     *
     * @return string|null
     */
    public function getUpdateType(): ?string;

    /**
     * Returns chat type of incoming Telegram update.
     *
     * @return string|null
     */
    public function getChatType(): ?string;

    /**
     * Returns user that caused the incoming Telegram update.
     *
     * @return object|null
     */
    public function getUser(): ?object;
}
