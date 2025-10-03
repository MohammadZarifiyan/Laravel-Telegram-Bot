<?php

namespace MohammadZarifiyan\Telegram\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use MohammadZarifiyan\Telegram\Channel;
use MohammadZarifiyan\Telegram\Console\Commands\MakeBreaker;
use MohammadZarifiyan\Telegram\Console\Commands\MakeCommandHandler;
use MohammadZarifiyan\Telegram\Console\Commands\MakeMiddleware;
use MohammadZarifiyan\Telegram\Console\Commands\MakeReplyMarkup;
use MohammadZarifiyan\Telegram\Console\Commands\MakeStage;
use MohammadZarifiyan\Telegram\Console\Commands\MakeUpdate;
use MohammadZarifiyan\Telegram\Interfaces\ProxyRepository as ProxyRepositoryInterface;
use MohammadZarifiyan\Telegram\Interfaces\Telegram as TelegramInterface;
use MohammadZarifiyan\Telegram\Interfaces\ApiKeyRepository as ApiKeyRepositoryInterface;
use MohammadZarifiyan\Telegram\Interfaces\EndpointRepository as EndpointRepositoryInterface;
use MohammadZarifiyan\Telegram\Interfaces\SecretTokenRepository as SecretTokenRepositoryInterface;
use MohammadZarifiyan\Telegram\Interfaces\PendingRequestStack as PendingRequestStackInterface;
use MohammadZarifiyan\Telegram\Interfaces\RequestParser as RequestParserInterface;
use MohammadZarifiyan\Telegram\PendingRequestStack;
use MohammadZarifiyan\Telegram\RequestParser;
use MohammadZarifiyan\Telegram\TelegramManager;

class TelegramServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register Telegram service.
     *
     * @return void
     */
    public function register(): void
    {
		$this->mergeConfigFrom(__DIR__.'/../../config/telegram.php', 'telegram');

        $this->app->bind(TelegramInterface::class, function (Application $application, array $parameters = []) {
            if (empty($parameters['apiKey'])) {
                /**
                 * @var ApiKeyRepositoryInterface $apiKeyRepository
                 */
                $apiKeyRepository = $application->make(ApiKeyRepositoryInterface::class);
                $apiKey = $apiKeyRepository->get();
            }
            else {
                $apiKey = $parameters['apiKey'];
            }

            if (empty($parameters['endpoint'])) {
                /**
                 * @var EndpointRepositoryInterface $endpointRepository
                 */
                $endpointRepository = $application->make(EndpointRepositoryInterface::class);
                $endpoint = $endpointRepository->get();
            }
            else {
                $endpoint = $parameters['endpoint'];
            }

            if (empty($parameters['secretToken'])) {
                /**
                 * @var SecretTokenRepositoryInterface $secretTokenRepository
                 */
                $secretTokenRepository = $application->make(SecretTokenRepositoryInterface::class);
                $secretToken = $secretTokenRepository->get();
            }
            else {
                $secretToken = $parameters['secretToken'];
            }

            return new TelegramManager($apiKey, $endpoint, $secretToken);
        });
	
		$this->app->bind(PendingRequestStackInterface::class, PendingRequestStack::class);
		
		$this->app->bind(RequestParserInterface::class, RequestParser::class);

        $this->app->bind(EndpointRepositoryInterface::class, config('telegram.endpoint-repository'));

        $this->app->bind(ApiKeyRepositoryInterface::class, config('telegram.api-key-repository'));

        $this->app->bind(SecretTokenRepositoryInterface::class, config('telegram.secret-token-repository'));

        $this->app->bind(ProxyRepositoryInterface::class, config('telegram.proxy-repository'));
    }
	
	/**
	 * Boots Telegram service.
	 *
	 * @return void
	 * @throws \Illuminate\Contracts\Container\BindingResolutionException
	 */
    public function boot(): void
    {
        $this->publish();

		$this->addConsoleCommands();
	
		$this->addNotificationChannel();
    }

    /**
     * Publishes anything that Telegram service needs
     *
     * @return void
     */
    public function publish(): void
    {
        $this->publishes(
			[__DIR__.'/../../config/telegram.php' => config_path('telegram.php')],
			'telegram-config'
		);
    }
	
	/**
	 * Adds console commands to the application.
	 *
	 * @return void
	 */
	public function addConsoleCommands(): void
	{
		if ($this->app->runningInConsole()) {
			$this->commands([
				MakeBreaker::class,
				MakeCommandHandler::class,
				MakeMiddleware::class,
				MakeReplyMarkup::class,
				MakeStage::class,
				MakeUpdate::class,
			]);
		}
	}
	
	/**
	 * Adds Telegram notification channel to the application.
	 *
	 * @return void
	 * @throws \Illuminate\Contracts\Container\BindingResolutionException
	 */
	public function addNotificationChannel(): void
	{
		Notification::resolved(function (ChannelManager $service) {
			$service->extend(
				'telegram',
				fn () => $this->app->make(Channel::class)
			);
		});
	}
	
	/**
	 * @return string[]
	 */
	public function provides(): array
	{
		return [
            'update',
            TelegramInterface::class,
            RequestParserInterface::class,
			PendingRequestStackInterface::class,
            EndpointRepositoryInterface::class,
            ApiKeyRepositoryInterface::class,
            SecretTokenRepositoryInterface::class,
		];
	}
}
