<?php

namespace MohammadZarifiyan\Telegram\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use MohammadZarifiyan\Telegram\Exceptions\TelegramException;
use MohammadZarifiyan\Telegram\Facades\Telegram;
use MohammadZarifiyan\Telegram\Interfaces\ApiKeyRepository;
use MohammadZarifiyan\Telegram\Interfaces\SecureTokenRepository;

class SetWebhook extends Command
{
    protected $signature = 'telegram:set-webhook {--drop-pending-updates} {--api-key=} {--url=} {--max-connections=40} {--secure-token=}';

    protected $description = 'Sets Telegram webhook.';

    public function handle()
    {
        try {
            $data = [
                'url' => $this->getUrl(),
                'drop_pending_updates' => $this->option('drop-pending-updates'),
                'max_connections' => $this->option('max-connections') ? (int) $this->option('max-connections') : 40
            ];

            if ($secure_token = $this->getSecureToken()) {
                $data['secure_token'] = $secure_token;
            }

            $response = Telegram::fresh($this->getApiKey())->perform('setWebhook', $data);

			$result = $response->object();

            if ($response->ok() && $result->ok) {
				$this->info('Webhook set successfully.');
				
				return 0;
            }

			$this->error('Failed to set webhook.');
			
			if (property_exists($result, 'description')) {
				$this->error($result->description);
			}

			return 1;
        }
        catch (TelegramException $exception) {
            $this->error(
				$exception->getMessage()
			);
	
			return 1;
        }
    }
	
	public function getUrl(): string
	{
        $url = $this->option('url');

        if (is_string($url) && strlen($url)) {
            return $url;
        }
		
		$route_name = Config::get('telegram.update-route');
		
		return Url::route($route_name);
	}
	
	public function getApiKey(): string
	{
        $api_key = $this->option('api-key');

        if (is_string($api_key) && strlen($api_key)) {
            return $api_key;
        }

        /**
         * @var ApiKeyRepository $api_key_repository
         */
        $api_key_repository = App::make(ApiKeyRepository::class);
        return $api_key_repository->get();
	}

    public function getSecureToken(): mixed
    {
        $secure_token = $this->option('secure-token');

        if (is_string($secure_token) && strlen($secure_token)) {
            return $secure_token;
        }

        /**
         * @var SecureTokenRepository $secure_token_repository
         */
        $secure_token_repository = App::make(SecureTokenRepository::class);
        return $secure_token_repository->get();
    }
}
