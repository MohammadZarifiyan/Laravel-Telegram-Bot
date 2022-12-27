<?php

namespace MohammadZarifiyan\Telegram\Providers;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use MohammadZarifiyan\Telegram\Channel;
use MohammadZarifiyan\Telegram\Console\Commands\MakeBreaker;
use MohammadZarifiyan\Telegram\Console\Commands\MakeCommandHandler;
use MohammadZarifiyan\Telegram\Console\Commands\MakeMiddleware;
use MohammadZarifiyan\Telegram\Console\Commands\MakePayload;
use MohammadZarifiyan\Telegram\Console\Commands\MakeReplyMarkup;
use MohammadZarifiyan\Telegram\Console\Commands\MakeStage;
use MohammadZarifiyan\Telegram\Console\Commands\MakeUpdate;
use MohammadZarifiyan\Telegram\Console\Commands\SetWebhook;
use MohammadZarifiyan\Telegram\Interfaces\PendingRequestStack as PendingRequestStackInterface;
use MohammadZarifiyan\Telegram\Interfaces\RequestParser as RequestParserInterface;
use MohammadZarifiyan\Telegram\Interfaces\Telegram as TelegramInterface;
use MohammadZarifiyan\Telegram\Middlewares\ChatTypeMiddleware;
use MohammadZarifiyan\Telegram\Middlewares\UpdateTypeMiddleware;
use MohammadZarifiyan\Telegram\PendingRequestStack;
use MohammadZarifiyan\Telegram\FormUpdate;
use MohammadZarifiyan\Telegram\RequestParser;
use MohammadZarifiyan\Telegram\Telegram;

class TelegramServiceProvider extends ServiceProvider implements DeferrableProvider
{
	/**
	 * Telegram update types.
	 */
	public const UPDATE_TYPES = [
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
    public function register(): void
    {
		$this->mergeConfigFrom(__DIR__.'/../../config/telegram.php', 'telegram');
		
        $this->app->singleton(
			TelegramInterface::class,
            fn (Container $app) => new Telegram(
				config('telegram.api-key'),
				config('telegram.endpoint')
			)
        );
	
		$this->app->bind('update-type', UpdateTypeMiddleware::class);
		
		$this->app->bind('chat-type', ChatTypeMiddleware::class);
		
		$this->app->bind(PendingRequestStackInterface::class, PendingRequestStack::class);
		
		$this->app->bind(RequestParserInterface::class, RequestParser::class);
	
		$this->addTelegramRequestResolver();
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

        $this->declareMacros();

		$this->aliasMiddlewares();

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

        $this->publishes(
			[__DIR__.'/../database/migrations' => database_path('migrations')],
			'telegram-migrations'
		);
    }

    /**
     * Declares service macros
     *
     * @return void
     */
    public function declareMacros(): void
    {
        Blueprint::macro('telegram', function () {
            static::bigInteger('telegram_id')->nullable();
            static::longText('stage')->nullable();
        });
    }

	/**
	 * Makes middleware aliases for entire app.
	 *
	 * @throws \Illuminate\Contracts\Container\BindingResolutionException
	 * @return void
	 */
	public function aliasMiddlewares(): void
	{
		$router = $this->app->make(Router::class);

		$router->aliasMiddleware('update-type', UpdateTypeMiddleware::class);
		$router->aliasMiddleware('chat-type', ChatTypeMiddleware::class);
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
				SetWebhook::class,
				MakeBreaker::class,
				MakeCommandHandler::class,
				MakeMiddleware::class,
				MakePayload::class,
				MakeReplyMarkup::class,
				MakeStage::class,
				MakeUpdate::class
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
	 * Adds Telegram Request resolver to the application.
	 *
	 * @return void
	 */
	public function addTelegramRequestResolver(): void
	{
		$this->app->resolving(
			FormUpdate::class,
			function ($update, Container $app) {
				$from = $app->has(TelegramInterface::class)
					? $app->get(TelegramInterface::class)->getUpdate()
					: $app['request'];
				
				FormUpdate::createFrom($from, $update)->setContainer($app);
			}
		);
	}
	
	/**
	 * @return string[]
	 */
	public function provides(): array
	{
		return [
			TelegramInterface::class,
			'update-type',
			'chat-type',
			PendingRequestStackInterface::class
		];
	}
}
