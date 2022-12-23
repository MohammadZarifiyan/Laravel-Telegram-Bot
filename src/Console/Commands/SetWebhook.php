<?php

namespace MohammadZarifiyan\Telegram\Console\Commands;

use Illuminate\Console\Command;
use MohammadZarifiyan\Telegram\Exceptions\TelegramException;
use MohammadZarifiyan\Telegram\Facades\Telegram;
use MohammadZarifiyan\Telegram\Payloads\SetWebhookPayload;

class SetWebhook extends Command
{
    protected $signature = 'bot:set-webhook {--drop-pending-updates} {--max-connections=40}';

    protected $description = 'Sets Telegram webhook.';

    public function handle()
    {
        try {
			$payload = new SetWebhookPayload(
				$this->hasOption('drop-pending-updates'),
				config('telegram.secure-token'),
				(int) $this->option('max-connections')
			);
			
            $response = Telegram::execute($payload);

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
}
