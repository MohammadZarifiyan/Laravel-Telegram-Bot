<?php

return [
	/**
	 * Telegram API endpoint.
	 */
	'endpoint' => 'https://api.telegram.org',
	
	/**
	 * Telegram API Key used for executing payloads.
	 *
	 * It can be blank if you are not executing any payload
	 * or sending any Telegram notification in your entire application.
	 */
	'api-key' => env('TELEGRAM_API_KEY'),
	
	/**
	 * Route name of Telegram update controller.
	 */
	'update-route' => 'telegram-update',
	
	/**
	 * Telegram secure token used to authorize HTTP requests.
	 *
	 * It highly recommended to define secure token.
	 */
	'secure-token' => env('TELEGRAM_SECURE_TOKEN'),
	
	/**
	 * Allow handling incognito command.
	 *
	 * If set to "false" and command was not exists in Telegram commands list,
	 * application would throw TelegramCommandNotFoundException.
	 */
	'allow-incognito-command' => false,
	
	/**
	 * Throw exception if executing payload failed.
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
