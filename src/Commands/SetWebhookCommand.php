<?php

namespace MohammadZarifiyan\Telegram\Commands;

use Illuminate\Console\Command;
use MohammadZarifiyan\Telegram\Exceptions\TelegramException;
use MohammadZarifiyan\Telegram\Facades\Telegram;
use MohammadZarifiyan\Telegram\Responses\SetWebhookResponse;
use Symfony\Component\HttpFoundation\Response;

class SetWebhookCommand extends Command
{
    protected $signature = 'bot:set-webhook {--drop-pending-updates=false}';

    protected $description = 'Sets Telegram webhook.';

    public function handle()
    {
        try {
			$response = new SetWebhookResponse(
				$this->hasOption('drop-pending-updates') && (is_null($this->option('drop-pending-updates')) || (bool) $this->option('drop-pending-updates') === true),
				config('services.telegram.secure_token')
			);
			
            $result = Telegram::sendResponse($response);

            if ($result->status() !== Response::HTTP_OK) {
                throw new TelegramException($result->object()->description ?? 'An error has occurred.');
            }

            $this->info('Webhook set successfully.');
        }
        catch (TelegramException $e) {
            $this->error($e->getMessage());
        }
    }
}
