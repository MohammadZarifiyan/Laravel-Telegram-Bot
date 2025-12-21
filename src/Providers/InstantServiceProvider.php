<?php

namespace MohammadZarifiyan\Telegram\Providers;

use Faker\Generator as FakerGenerator;
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
use MohammadZarifiyan\Telegram\FakerProviders\TelegramBotApiKeyProvider;

class InstantServiceProvider extends ServiceProvider
{
    /**
     * Register Telegram service.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/telegram.php', 'telegram');

        $this->app->extend(FakerGenerator::class, function (FakerGenerator $faker) {
            $faker->addProvider(new TelegramBotApiKeyProvider($faker));

            return $faker;
        });
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

        $this->addNotificationChannel();

        $this->addConsoleCommands();
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
     * Adds console commands to the application.
     *
     * @return void
     */
    public function addConsoleCommands(): void
    {
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
