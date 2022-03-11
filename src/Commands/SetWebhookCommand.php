<?php

namespace MohammadZarifiyan\Telegram\Commands;

use Exception;
use Illuminate\Console\Command;
use MohammadZarifiyan\Telegram\Facades\Telegram;
use MohammadZarifiyan\Telegram\Responses\SetWebhookResponse;
use Symfony\Component\HttpFoundation\Response;

class SetWebhookCommand extends Command
{
    protected $signature = 'bot:set-webhook';

    protected $description = 'Sets Telegram webhook.';

    public function handle()
    {
        try {
            $response = Telegram::sendResponse(SetWebhookResponse::class);

            if ($response->status() !== Response::HTTP_OK) {
                throw new Exception($response->object()->description ?? 'An error has occurred.');
            }

            $this->info('Webhook set successfully.');
        }
        catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
