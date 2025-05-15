<?php

return [
    /**
     * Settings for retrying requests in case of connection errors
     */
    'retry' => [
        'times' => 5,
        'sleep' => 200,
    ],

    /**
     * Proxy repository
     *
     * It must be an instance of \MohammadZarifiyan\Telegram\Interfaces\ProxyRepository
     */
    'proxy-repository' => \MohammadZarifiyan\Telegram\Repositories\ProxyRepository::class,

    /**
     * status of TLS Certificate endpoint verification
     *
     * It is recommended to set this to true
     */
    'verify-endpoint' => (bool) env('TELEGRAM_VERIFY_ENDPOINT', true),

	/**
	 * Telegram API endpoint repository.
     *
     * It must be an instance of \MohammadZarifiyan\Telegram\Interfaces\EndpointRepository
	 */
	'endpoint-repository' => \MohammadZarifiyan\Telegram\Repositories\EndpointRepository::class,
	
	/**
	 * Telegram API Key repository.
     *
     * It must be an instance of \MohammadZarifiyan\Telegram\Interfaces\ApiKeyRepository
	 */
	'api-key-repository' => \MohammadZarifiyan\Telegram\Repositories\ApiKeyRepository::class,

    /**
     * An instance of \MohammadZarifiyan\Telegram\Interfaces\PendingRequest that accepts \MohammadZarifiyan\Telegram\Interfaces\PendingRequest as its constructor parameter.
     */
    'pending-request-manipulator' => null,
	
	/**
	 * Telegram secret token repository.
     *
     * It must be an instance of \MohammadZarifiyan\Telegram\Interfaces\EndpointRepository
	 */
	'secret-token-repository' => \MohammadZarifiyan\Telegram\Repositories\SecretTokenRepository::class,
	
	/**
	 * Allow handling incognito command.
	 *
	 * If set to "false" and command was not exists in Telegram commands list,
	 * application would throw TelegramCommandHandlerNotFoundException.
	 */
	'allow-incognito-command' => false,
	
	/**
	 * Throw exception if performing HTTP request failed.
	 *
	 * Default is "false" to prevent retrieving Telegram update again and again
	 * when handling Telegram updates through Telegram webhook.
	 */
	'throw-http-exception' => false,
	
	/**
	 * Gainer resolver used to resolve gainer from Telegram updates.
	 */
	'gainer-resolver' => null,
	
	/**
	 * A list of Telegram middlewares will run before handling any command.
	 * All of them should return request or application would throw TelegramMiddlewareFailedException.
	 *
	 * It is better to keep this list short to prevent performance issues.
	 */
	'middlewares' => [],
	
	/**
	 * List of command handlers that should be used to handle Telegram bot commands.
	 */
	'command_handlers' => [],
	
	/**
	 * An array of breakers classes that run before stage handler.
	 * If all breakers return "false" then stage handler will execute.
	 *
	 * It is better to keep this list short to prevent performance issues.
	 */
	'breakers' => [],
];
