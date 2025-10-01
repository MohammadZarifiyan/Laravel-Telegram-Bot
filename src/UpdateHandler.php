<?php

namespace MohammadZarifiyan\Telegram;

use Exception;
use Generator;
use Illuminate\Support\Str;
use MohammadZarifiyan\Telegram\Exceptions\TelegramCommandHandlerNotFoundException;
use MohammadZarifiyan\Telegram\Exceptions\TelegramException;
use MohammadZarifiyan\Telegram\Exceptions\TelegramMiddlewareFailedException;
use MohammadZarifiyan\Telegram\Exceptions\TelegramOriginException;
use MohammadZarifiyan\Telegram\Interfaces\AnonymousCommandHandler;
use MohammadZarifiyan\Telegram\Interfaces\CommandHandler;
use MohammadZarifiyan\Telegram\Interfaces\HasStage;
use ReflectionException;
use ReflectionMethod;

class UpdateHandler
{
	public function __construct(public Update $update, protected ?string $secretToken = null)
	{
		//
	}
	
	/**
	 * @throws TelegramException
	 * @throws TelegramOriginException
	 * @throws ReflectionException
	 */
	public function run(): Generator
	{
		$this->validateOrigin();
		
		yield from $this->runMiddlewares();
		
		if ($this->update->isCommand()) {
			if (empty($command = $this->getMatchedCommand()) && !config('telegram.allow-incognito-command')) {
				throw new TelegramCommandHandlerNotFoundException(
					$this->update->toCommand()
				);
			}
			
			$command->handle($this->update);
			
			return;
		}
		
		if ($this->runBreakers()) {
			return;
		}
		
		$this->handleStage();
	}
	
	/**
	 * Validates specified request to have valid origin.
	 *
	 * @return void
	 * @throws TelegramOriginException
	 */
	public function validateOrigin(): void
	{
        if (empty($this->secretToken)) {
            return;
        }

		if (trim($this->secretToken) === trim((string) $this->update->header('X-Telegram-Bot-Api-Secret-Token'))) {
			return;
		}
		
		throw new TelegramOriginException('Telegram update is not from authorized origin.', 401);
	}
	
	/**
	 * @return Generator
	 * @throws TelegramMiddlewareFailedException
	 */
	public function runMiddlewares(): Generator
	{
        foreach ((array) config('telegram.middlewares') as $middleware) {
            $middleware = try_resolve($middleware);

            $method = $this->getMethod($middleware);

            if (empty($method)) {
                continue;
            }

            $result = $middleware->{$method}($this->update);

            if ($result instanceof Update) {
                yield $this->update = $result;

                continue;
            }

            throw new TelegramMiddlewareFailedException(
                sprintf('Telegram middleware %s failed.', get_class($middleware))
            );
        }
	}
	
	/**
	 * Returns validated request if exists, otherwise returns initial request.
	 *
	 * @param $stage
	 * @param string $method
	 * @return ?Update
	 * @throws ReflectionException
	 */
	public function getValidatedRequest($stage, string $method): ?Update
	{
		$reflection = new ReflectionMethod($stage, $method);
		
		if (empty($parameters = $reflection->getParameters())) {
			return $this->update;
		}
		
		$requestType = $parameters[0]->getType();
		
		if (empty($requestType)) {
			return null;
		}
		
		if (is_subclass_of($requestType->getName(), FormUpdate::class)) {
            return $requestType->getName()::createFrom($this->update);
		}
		
		return $this->update;
	}
	
	public function getMethod(object $object): ?string
	{
		$handlerMethod = $this->getHandlerMethod();
		
		if (method_exists($object, $handlerMethod)) {
			return $handlerMethod;
		}
		
		if (method_exists($object, 'handle')) {
			return 'handle';
		}
		
		return null;
	}
	
	public function getMatchedCommand(): null|CommandHandler|AnonymousCommandHandler
	{
		foreach ((array) config('telegram.command_handlers') as $commandHandler) {
			/**
			 * @var CommandHandler|null $commandHandlerInstance
			 */
			$commandHandlerInstance = try_resolve($commandHandler);

            if ($commandHandlerInstance instanceof CommandHandler) {
                $signature = $this->update->toCommand()->getSignature();

                if (in_array($signature, (array) $commandHandlerInstance->getSignature($this->update))) {
                    return $commandHandlerInstance;
                }
            }
            else if ($commandHandlerInstance instanceof AnonymousCommandHandler) {
                if ($commandHandlerInstance->matchesSignature($this->update)) {
                    return $commandHandlerInstance;
                }
            }
            else {
                throw new Exception(
                    sprintf('(%s) is not a valid command handler', (string) $commandHandler)
                );
            }
		}
		
		return null;
	}
	
	/**
	 * Handle update using available breakers.
	 */
	public function runBreakers(): bool
	{
        foreach ((array) config('telegram.breakers') as $breaker) {
            $breaker = try_resolve($breaker);

            $method = $this->getMethod($breaker);

            if ($method && $breaker->{$method}($this->update)) {
                return true;
            }
        }
		
		return false;
	}
	
	/**
	 * Handle update using available handlers.
	 *
	 * @throws ReflectionException
	 */
	public function handleStage(): void
	{
		$gainer = $this->update->gainer();
		
		if (!($gainer instanceof HasStage)) {
			return;
		}
		
		$stage = try_resolve($gainer->getStage());
		
		if (empty($stage) || empty($method = $this->getMethod($stage)) || !method_exists($stage, $method)) {
			return;
		}
		
		$stage->{$method}(
			$this->getValidatedRequest($stage, $method)
		);
	}
	
	public function getHandlerMethod(): string
	{
		return sprintf(
			'handle%s',
			Str::studly($this->update->type())
		);
	}
}
