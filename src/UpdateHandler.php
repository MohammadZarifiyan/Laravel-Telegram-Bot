<?php

namespace MohammadZarifiyan\Telegram;

use Generator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use MohammadZarifiyan\Telegram\Exceptions\TelegramCommandHandlerNotFoundException;
use MohammadZarifiyan\Telegram\Exceptions\TelegramException;
use MohammadZarifiyan\Telegram\Exceptions\TelegramMiddlewareFailedException;
use MohammadZarifiyan\Telegram\Exceptions\TelegramOriginException;
use MohammadZarifiyan\Telegram\Interfaces\CacheManager;
use MohammadZarifiyan\Telegram\Interfaces\CanGetCommand;
use MohammadZarifiyan\Telegram\Interfaces\Command;
use MohammadZarifiyan\Telegram\Interfaces\CommandHandler;
use MohammadZarifiyan\Telegram\Interfaces\Gainer;
use MohammadZarifiyan\Telegram\Interfaces\SecureTokenRepository;
use ReflectionException;
use ReflectionMethod;

class UpdateHandler
{
	public function __construct(public Update $update)
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
        /**
         * @var SecureTokenRepository $secure_token_repository
         */
        $secure_token_repository = App::make(SecureTokenRepository::class);

        if (empty($secure_token = $secure_token_repository->get())) {
            return;
        }

		if (trim($secure_token) === trim((string) $this->update->header('X-Telegram-Bot-Api-Secret-Token'))) {
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
        /**
         * @var CacheManager $cache_manager
         */
        $cache_manager = App::make(CacheManager::class);

        if ($cache_manager->exists('middlewares.json')) {
            $grouped_middlewares = json_decode($cache_manager->get('middlewares.json'), true);
            $update_type = $this->update->type();

            if (array_key_exists($update_type, $grouped_middlewares)) {
                $handler_method = $this->getHandlerMethod();

                foreach ($grouped_middlewares[$update_type] as $middleware) {
                    $middleware = try_resolve($middleware);
                    $result = $middleware->{$handler_method}($this->update);

                    if ($result instanceof Update) {
                        yield $this->update = $result;

                        continue;
                    }

                    throw new TelegramMiddlewareFailedException(
                        sprintf('Telegram middleware %s failed.', get_class($middleware))
                    );
                }
            }

            foreach ($grouped_middlewares['all'] ?? [] as $middleware) {
                $middleware = try_resolve($middleware);
                $result = $middleware->handle($this->update);

                if ($result instanceof Update) {
                    yield $this->update = $result;

                    continue;
                }

                throw new TelegramMiddlewareFailedException(
                    sprintf('Telegram middleware %s failed.', get_class($middleware))
                );
            }
        }
        else {
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
		
		$request_type = $parameters[0]->getType();
		
		if (empty($request_type)) {
			return null;
		}
		
		if (is_subclass_of($request_type->getName(), FormUpdate::class)) {
			return App::make($request_type->getName());
		}
		
		return $this->update;
	}
	
	public function getMethod(object $object): ?string
	{
		$handler_method = $this->getHandlerMethod();
		
		if (method_exists($object, $handler_method)) {
			return $handler_method;
		}
		
		if (method_exists($object, 'handle')) {
			return 'handle';
		}
		
		return null;
	}
	
	public function getMatchedCommand(): ?CommandHandler
	{
		$default_command = $this->update->toCommand();
		
		foreach ((array) config('telegram.command_handlers') as $command_handler) {
			/**
			 * @var CommandHandler|null $command_handler
			 */
			$command_handler = try_resolve($command_handler);
			
			$command = $default_command;
			
			if ($command_handler instanceof CanGetCommand) {
				$possible_command = $command_handler->getCommand($this->update);
				
				if ($possible_command instanceof Command) {
					$command = $possible_command;
				}
			}
			
			if (in_array($command->getSignature(), (array) $command_handler->getSignature($this->update))) {
				$this->update->setCommand($command);
				
				return $command_handler;
			}
		}
		
		return null;
	}
	
	/**
	 * Handle update using available breakers.
	 */
	public function runBreakers(): bool
	{
        /**
         * @var CacheManager $cache_manager
         */
        $cache_manager = App::make(CacheManager::class);

        if ($cache_manager->exists('breakers.json')) {
            $grouped_breakers = json_decode($cache_manager->get('breakers.json'), true);
            $update_type = $this->update->type();

            if (array_key_exists($update_type, $grouped_breakers)) {
                $handler_method = $this->getHandlerMethod();

                foreach ($grouped_breakers[$update_type] as $breaker) {
                    $breaker = try_resolve($breaker);

                    if ($breaker->{$handler_method}($this->update)) {
                        return true;
                    }
                }
            }

            foreach ($grouped_breakers['all'] ?? [] as $breaker) {
                $breaker = try_resolve($breaker);

                if ($breaker->handle($this->update)) {
                    return true;
                }
            }
        }
        else {
            foreach ((array) config('telegram.breakers') as $breaker) {
                $breaker = try_resolve($breaker);

                $method = $this->getMethod($breaker);

                if ($method && $breaker->{$method}($this->update)) {
                    return true;
                }
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
		
		if (!($gainer instanceof Gainer)) {
			return;
		}
		
		$stage = try_resolve(
			$gainer->{$gainer->getStageColumnName()}
		);
		
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
