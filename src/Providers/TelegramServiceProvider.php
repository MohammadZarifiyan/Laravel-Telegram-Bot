<?php

namespace MohammadZarifiyan\Telegram\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use MohammadZarifiyan\Telegram\Commands\SetWebhookCommand;
use MohammadZarifiyan\Telegram\Middlewares\ChatTypeMiddleware;
use MohammadZarifiyan\Telegram\Middlewares\UpdateTypeMiddleware;
use MohammadZarifiyan\Telegram\TelegramRequest;

class TelegramServiceProvider extends ServiceProvider implements DeferrableProvider
{
	/**
	 * Telegram update types.
	 */
	const UPDATE_TYPES = [
		'message',
		'edited_message',
		'channel_post',
		'edited_channel_post',
		'inline_query',
		'chosen_inline_result',
		'callback_query',
		'shipping_query',
		'pre_checkout_query',
		'poll',
		'poll_answer',
		'my_chat_member',
		'chat_member',
		'chat_join_request',
	];
	
    /**
     * Register Telegram service.
     *
     * @return void
     */
    public function register()
    {
		$this->mergeConfigFrom(
			__DIR__.'/../../config/telegram.php', 'telegram'
		);
		
        $this->app->bind(
            'telegram.service',
            fn () => $this->app
				->make(config('telegram.service'))
				->setApiKey(config('services.telegram.api_key'))
        );

        $this->app->bind(
			'telegram.kernel',
            config('telegram.kernel')
        );
	
		$this->app->bind('update-type', UpdateTypeMiddleware::class);
		
		$this->app->bind('chat-type', ChatTypeMiddleware::class);
    }
	
	/**
	 * Boots Telegram service.
	 *
	 * @return void
	 * @throws \Illuminate\Contracts\Container\BindingResolutionException
	 */
    public function boot()
    {
        $this->publish();

        $this->declareMacros();

        if ($this->app->runningInConsole()) {
            $this->commands([
                SetWebhookCommand::class
            ]);
        }

		$this->makeMiddlewareAliases();
	
		$this->app->resolving(TelegramRequest::class, function ($request, $app) {
			$request = TelegramRequest::createFrom($app['request'], $request);
		
			$request->setContainer($app);
		});
    }

    /**
     * Publishes anything that Telegram service needs
     *
     * @return void
     */
    public function publish()
    {
        $this->publishes([
            __DIR__.'/../../config/telegram.php' => config_path('telegram.php')
        ], 'telegram-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations')
        ], 'telegram-migrations');

        $this->publishes([
            __DIR__.'/../Kernel.php' => app_path('Telegram/Kernel.php')
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
            static::text('handler')->nullable()->comment('Full classname of current responsible handler');
        });
    }

	/**
	 * Makes middleware aliases for entire app.
	 *
	 * @throws \Illuminate\Contracts\Container\BindingResolutionException
	 */
	public function makeMiddlewareAliases()
	{
		$router = $this->app->make(Router::class);

		$router->aliasMiddleware('update-type', UpdateTypeMiddleware::class);
		$router->aliasMiddleware('chat-type', ChatTypeMiddleware::class);
	}
	
	public function provides()
	{
		return ['telegram', 'telegram.kernel', 'update-type', 'chat-type'];
	}
}
