<?php

namespace MohammadZarifiyan\Telegram\Providers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;
use MohammadZarifiyan\Telegram\Commands\SetWebhookCommand;

class TelegramServiceProvider extends ServiceProvider
{
    /**
     * Register Telegram service.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            \MohammadZarifiyan\Telegram\Interfaces\Telegram::class,
            config('telegram.service')
        );

        $this->app->singleton(
            \MohammadZarifiyan\Telegram\Kernel::class,
            config('telegram.kernel')
        );

        $this->mergeConfigFrom(__DIR__.'/../config/telegram.php', 'telegram');
    }

    /**
     * Boots Telegram service.
     *
     * @return void
     */
    public function boot()
    {
        $this->publish();

        $this->declareMacros();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                SetWebhookCommand::class
            ]);
        }
    }

    /**
     * Publishes anything that Telegram service needs
     *
     * @return void
     */
    public function publish()
    {
        $this->publishes([
            __DIR__.'../config/telegram.php' => function_exists('config_path') ? config_path('telegram.php') : base_path('config/telegram.php')
        ], 'telegram-config');

        $this->publishes([
            __DIR__.'../database/migrations' => database_path('migrations')
        ], 'telegram-migrations');

        $this->publishes([
            __DIR__.'../Kernel.php' => function_exists('app_path') ? app_path('Telegram/Kernel.php') : base_path('app/Telegram/Kernel.php')
        ], 'telegram-kernel');
    }

    /**
     * Declares service macros
     *
     * @return void
     */
    public function declareMacros()
    {
        Blueprint::macro('telegram', function () {
            static::bigInteger('telegram_id')->nullable();
            static::string('handler')->nullable()->comment('Full classname of current responsible handler');
        });
    }
}
