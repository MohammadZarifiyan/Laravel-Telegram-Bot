<?php

namespace MohammadZarifiyan\Telegram\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use MohammadZarifiyan\Telegram\GainerManager;
use MohammadZarifiyan\Telegram\Interfaces\MockManager as MockManagerInterface;
use MohammadZarifiyan\Telegram\Interfaces\GainerManager as GainerManagerInterface;
use MohammadZarifiyan\Telegram\Interfaces\GainerResolver;
use MohammadZarifiyan\Telegram\Interfaces\ProxyRepository as ProxyRepositoryInterface;
use MohammadZarifiyan\Telegram\Interfaces\Telegram as TelegramInterface;
use MohammadZarifiyan\Telegram\Interfaces\ApiKeyRepository as ApiKeyRepositoryInterface;
use MohammadZarifiyan\Telegram\Interfaces\EndpointRepository as EndpointRepositoryInterface;
use MohammadZarifiyan\Telegram\Interfaces\SecretTokenRepository as SecretTokenRepositoryInterface;
use MohammadZarifiyan\Telegram\MockManager;
use MohammadZarifiyan\Telegram\TelegramManager;

class DeferredServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register Telegram service.
     *
     * @return void
     */
    public function register(): void
    {
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
	
        $this->app->bind(EndpointRepositoryInterface::class, config('telegram.endpoint-repository'));

        $this->app->bind(ApiKeyRepositoryInterface::class, config('telegram.api-key-repository'));

        $this->app->bind(SecretTokenRepositoryInterface::class, config('telegram.secret-token-repository'));

        $this->app->bind(ProxyRepositoryInterface::class, config('telegram.proxy-repository'));

        $this->app->bind(GainerResolver::class, config('telegram.gainer-resolver'));

        $this->app->scoped(GainerManagerInterface::class, GainerManager::class);

        $this->app->scoped(MockManagerInterface::class, MockManager::class);
    }
	
	/**
	 * @return string[]
	 */
	public function provides(): array
	{
		return [
            TelegramInterface::class,
            EndpointRepositoryInterface::class,
            ApiKeyRepositoryInterface::class,
            SecretTokenRepositoryInterface::class,
            ProxyRepositoryInterface::class,
            GainerResolver::class,
            GainerManagerInterface::class,
            MockManagerInterface::class,
		];
	}
}
