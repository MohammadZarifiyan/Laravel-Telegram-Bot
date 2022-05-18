<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Database\Eloquent\Model;

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
	 * Changes API key for running request lifecycle.
	 *
	 * @param string $token
	 * @return Telegram
	 */
    public function setApiKey(string $token): static;
	
	/**
	 * Returns current API key.
	 *
	 * @return string|null
	 */
	public function getApiKey(): ?string;

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

    /**
     * Returns gainer that caused the incoming Telegram update.
     *
     * @return object|null
     */
    public function getGainer(): ?Model;
	
	/**
	 * Sets gainer that caused the incoming Telegram update.
	 *
	 * @param Model $gainer
	 * @return Telegram
	 */
    public function setGainer(Model $gainer): static;

	/**
	 * Determines that request is a bot command update or not.
	 *
	 * @return bool
	 */
	public function isCommand(): bool;

	/**
	 * Returns command signature based on request.
	 *
	 * @return string|null
	 */
	public function commandSignature(): ?string;
	
	/**
	 * Generates file download URL from file path.
	 *
	 * @param string $filePath
	 * @return string
	 */
	public function generateFileUrl(string $filePath): string;
}
