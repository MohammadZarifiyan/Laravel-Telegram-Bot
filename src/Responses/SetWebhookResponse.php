<?php

namespace MohammadZarifiyan\Telegram\Responses;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use MohammadZarifiyan\Telegram\Interfaces\Response;

class SetWebhookResponse implements Response
{
	public function __construct(protected bool $dropPendingUpdates, protected ?string $secureToken = null)
	{
		//
	}
	
    public function method(Request $request, ?Model $gainer): string
    {
        return 'setWebhook';
    }

    public function data(Request $request, ?Model $gainer): array
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
