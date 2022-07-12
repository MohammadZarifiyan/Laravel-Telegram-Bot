<?php

namespace MohammadZarifiyan\Telegram\Responses;

use MohammadZarifiyan\Telegram\Interfaces\Response;

class SetWebhookResponse implements Response
{
	public function __construct(protected string|bool $dropPendingUpdates, protected ?string $secureToken = null)
	{
		//
	}
	
    public function method(): string
    {
        return 'setWebhook';
    }

    public function data(): array
    {
		$data = [
			'url' => route(config('services.telegram.update-route')),
			'drop_pending_updates' => $this->dropPendingUpdates
		];
		
		if ($this->secureToken) {
			$data['secret_token'] = $this->secureToken;
		}
		
        return $data;
    }
}
