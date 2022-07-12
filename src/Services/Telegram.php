<?php

namespace MohammadZarifiyan\Telegram\Services;

use Exception;
use GuzzleHttp\Promise\Promise;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use MohammadZarifiyan\Telegram\Interfaces\Response;
use MohammadZarifiyan\Telegram\Traits\HasReplyMarkup;

class Telegram implements \MohammadZarifiyan\Telegram\Interfaces\Telegram
{
	protected string $apiKey;
	
	protected Model $gainer;

    /**
     * @throws Exception
     */
    public function __construct(public Request $request)
    {
        //
    }

	public function setApiKey(string $token): self
	{
		$this->apiKey = $token;

		return $this;
	}

	public function getApiKey(): ?string
	{
		return @$this->apiKey;
	}

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function sendResponse(Response|string $response): ClientResponse
    {
        return $this->getPreparedRequest($response);
    }

    public function sendAsyncResponses(array $responses): array
    {
        return Http::pool(fn(Pool $pool) => array_map(
            fn (Response $response) => $this->getPreparedRequest($response, $pool),
            $responses
        ));
    }

    /**
     * Returns base URL for sending response using Telegram API.
     *
     * @return string
     * @throws Exception
     */
    private function getBaseUrl(): string
    {
        if (!isset($this->apiKey)) {
            throw new Exception('Telegram API Key is not set.');
        }

        return sprintf('https://api.telegram.org/bot%s', $this->apiKey);
    }

    /**
     * Returns resolved response
     *
     * @param Response|string $response
     * @return Response
     */
    public function getResolvedResponse(Response|string $response): Response
    {
        return is_string($response) ? App::make($response) : $response;
    }

    /**
     * Merges all needed parameters to return response body
     *
     * @param Response $response
     * @return array
     */
    public function getResponseBody(Response $response): array
    {
        return in_array(HasReplyMarkup::class, class_uses_recursive($response))
            ? $response->resolveWithReplayMarkup()
            : $response->data();
    }

	public function getPreparedRequest(Response|string $response, ?Pool $client = null): Promise|ClientResponse|array
    {
		$base_url = $this->getBaseUrl();

        $pending_request = $client ? $client->baseUrl($base_url) : Http::baseUrl($base_url);

        $resolved_response = $this->getResolvedResponse($response);

        return $pending_request->post($resolved_response->method(), $this->getResponseBody($resolved_response));
    }

    public function getUpdateType(): ?string
    {
        return collect($this::UPDATE_TYPES)
            ->intersect($this->request->keys())
            ->first();
    }

    public function getChatType(): ?string
    {
        $update_type = $this->getUpdateType();

        return match($update_type) {
            'message', 'edited_message', 'my_chat_member', 'chat_member', 'chat_join_request' => $this->request->input(sprintf('%s.chat.type', $update_type)),
            'channel_post', 'edited_channel_post' => 'channel',
            'inline_query', 'chosen_inline_result', 'callback_query', 'shipping_query', 'pre_checkout_query', 'poll_answer' => 'private',
			default => null
        };
    }

    public function getUser(): ?object
    {
        $update_type = $this->getUpdateType();

        $match = match($update_type) {
            'poll' => null,
            default => $this->request->input(sprintf('%s.from', $update_type)),
        };

        return $match ? (object) $match : null;
    }

	public function isCommand(): bool
	{
		$types = $this->request->input('message.entities.*.type');

		return $types && count($types) && in_array('bot_command', $types);
	}

	public function commandSignature(): ?string
	{
		if ($this->isCommand()) {
			$text = $this->request->input('message.text');

			return substr(
				explode(' ', $text)[0],
				1
			);
		}

		return null;
	}
	
	public function getGainer(): ?Model
	{
		return @$this->gainer;
	}
	
	public function setGainer(Model $gainer): self
	{
		$this->gainer = $gainer;
		
		return $this;
	}
	
	public function generateFileUrl(string $filePath): string
	{
		return sprintf('https://api.telegram.org/file/bot%s/%s', $this->getApiKey(), $filePath);
	}
}
