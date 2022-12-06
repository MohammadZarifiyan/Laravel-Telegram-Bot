<?php

namespace MohammadZarifiyan\Telegram\Abstractions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use MohammadZarifiyan\Telegram\Exceptions\TelegramCommandNotFoundException;
use MohammadZarifiyan\Telegram\Exceptions\TelegramException;
use MohammadZarifiyan\Telegram\Exceptions\TelegramMiddlewareFailedException;
use MohammadZarifiyan\Telegram\Exceptions\TelegramOriginException;
use MohammadZarifiyan\Telegram\Facades\Telegram;
use MohammadZarifiyan\Telegram\TelegramRequest;
use ReflectionException;
use ReflectionMethod;

abstract class Kernel
{
    /**
     * Handles incoming Telegram update.
     *
     * @param Request $request
     * @throws TelegramException
     */
    public function handleUpdate(Request $request): void
	{
		$this->validateOrigin($request);
		
        if (!Telegram::getUpdateType()) {
            throw new TelegramException('This request doesnt have any valid Telegram update.');
        }

        if ($gainer = $this->getGainer($request)) {
			Telegram::setGainer($gainer);
		}

		$request = $this->runMiddlewares($request, $gainer);

		if (Telegram::isCommand()) {
			if (empty($command = $this->getMatchedCommand()) && !$this->passIfCommandNotExists($request, $gainer)) {
				throw new TelegramCommandNotFoundException;
			}
			
			$command->handle($request, $gainer);

			return;
		}
		
		if ($this->runBreakers($request, $gainer)) {
			return;
		}

		if ($gainer) {
			$this->runHandler($request, $gainer);
		}
    }
	
	/**
	 * Validates specified request to have valid origin.
	 *
	 * @param Request $request
	 * @return void
	 * @throws TelegramOriginException
	 */
	private function validateOrigin(Request $request): void
	{
		$secure_token = config('services.telegram.secure_token');
		
		$secret_token = $request->header('X-Telegram-Bot-Api-Secret-Token');
		
		if (is_string($secure_token) && is_string($secret_token) && trim($secure_token) === trim($secret_token)) {
			return;
		}
		
		throw new TelegramOriginException('Incoming request is not from authorized origin.', 401);
	}

	/**
	 * Returns validated request if exists, otherwise returns initial request.
	 *
	 * @param $handler
	 * @param string $method
	 * @param Request $request
	 * @return Request
	 * @throws ReflectionException
	 */
	private function getValidatedRequest($handler, string $method, Request $request): Request
	{
		$parameters = (new ReflectionMethod($handler, $method))->getParameters();

		$request_type = $parameters[0]->getType();

		if ($request_type && is_subclass_of($request_type->getName(), TelegramRequest::class)) {
			$prepared_request = App::make($request_type->getName());
		}

		return $prepared_request ?? $request;
	}

	/**
	 * Calls update handler method on handler class.
	 *
	 * @param $handler
	 * @param string $method
	 * @param Request $request
	 * @param Model $gainer
	 */
	private function callHandlerMethod($handler, string $method, Request $request, Model $gainer): void
	{
		if (method_exists($handler, $method)) {
			try {
				$verified_request = $this->getValidatedRequest($handler, $method, $request);
			}
			catch (ReflectionException) {
				return;
			}

			$handler->{$method}($verified_request, $gainer);
		}
	}
	
	private function getMethod(object $object, string $updateHandlerMethod): ?string
	{
		if (method_exists($object, $updateHandlerMethod)) {
			return $updateHandlerMethod;
		}
		
		if (method_exists($object, 'handle')) {
			return 'handle';
		}
		
		return null;
	}

	/**
	 * Get or create the gainer that handlers should work with
	 *
	 * @param Request $request
	 * @return Model|null
	 */
    abstract public function getGainer(Request $request): ?Model;
	
	/**
	 * An array of middlewares classes that run before commands.
	 * if all middlewares returns true then request continues to processing.
	 *
	 * @return array
	 */
	abstract public function middlewares(): array;

    /**
     * An array of Telegram command classes
     *
     * @return array
     */
    abstract public function commands(): array;

    /**
     * An array of breakers classes that run before handlers and don't care about which handler should be used.
     * if all breakers returns false then handlers will execute
     *
     * @return array
     */
    abstract public function breakers(): array;
	
	private function getMatchedCommand(): ?Command
	{
		$command_signature = Telegram::commandSignature();
		
		foreach ($this->commands() as $command) {
			$command_instance = $command instanceof Command ? $command : try_resolve($command);
			
			if ($command_instance?->signature === $command_signature) {
				return $command_instance;
			}
		}
		
		return null;
	}
	
	/**
	 * method that uses to handle update
	 */
	private function requestHandleMethod(): string
	{
		return sprintf(
			'handle%s',
			Str::studly(Telegram::getUpdateType())
		);
	}
	
	/**
	 * Handle update using available breakers.
	 */
	private function runBreakers(Request $request, ?Model $gainer): bool
	{
		foreach ($this->breakers() as $breaker) {
			$method = $this->getMethod($breaker, $this->requestHandleMethod());
			
			if ($method && $breaker->{$method}($request, $gainer)) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Handle update using available handlers.
	 */
	private function runHandler(Request $request, Model $gainer): void
	{
		if (empty($gainer->handler)) {
			return;
		}
		
		$resolved_handler = try_resolve($gainer->handler);
		
		if ($method = $this->getMethod($resolved_handler, $this->requestHandleMethod())) {
			$this->callHandlerMethod(
				$resolved_handler,
				$method,
				$request,
				$gainer
			);
		}
	}
	
	/**
	 * Run request through another ways if command not exists.
	 *
	 * @param Request $request
	 * @param Model|null $gainer
	 * @return bool
	 */
	public function passIfCommandNotExists(Request $request, ?Model $gainer): bool
	{
		return false;
	}
	
	/**
	 * @throws TelegramMiddlewareFailedException
	 */
	private function runMiddlewares(Request $request, ?Model $gainer): Request
	{
		foreach ($this->middlewares() as $middleware) {
			$method = $this->getMethod($middleware, $this->requestHandleMethod());
			
			if (empty($method)) {
				continue;
			}
			
			$request = $middleware->{$method}($request, $gainer);
			
			if (!($request instanceof Request)) {
				throw new TelegramMiddlewareFailedException(
					sprintf('Telegram middleware [%] failed.', get_class($middleware))
				);
			}
		}
		
		return $request;
	}
}
