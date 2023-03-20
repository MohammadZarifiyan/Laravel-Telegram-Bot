<?php

namespace MohammadZarifiyan\Telegram\Payloads;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use MohammadZarifiyan\Telegram\Interfaces\Payload;

class SetWebhookPayload implements Payload
{
	public function __construct(
		protected string $url,
		protected bool $dropPendingUpdates,
		protected ?string $secureToken = null,
		protected int $maxConnections = 40
	) {
		//
	}
	
    public function method(): string
    {
        return 'setWebhook';
    }

    public function data(): array
    {
		$data = [
			'url' => $this->url,
			'drop_pending_updates' => $this->dropPendingUpdates,
			'max_connections' => $this->maxConnections
		];
		
		if ($this->secureToken) {
			$data['secret_token'] = $this->secureToken;
		}
		
        return $data;
    }
}
