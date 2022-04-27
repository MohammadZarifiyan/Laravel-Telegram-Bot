<?php

namespace MohammadZarifiyan\Telegram\Abstractions;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
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
     * @throws Exception
     */
    public function handleUpdate(Request $request)
    {
        if (!Telegram::getUpdateType()) {
            throw new Exception('This request doesnt have any valid Telegram update.');
        }

        $gainer = $this->getGainer($request);
		
		Telegram::setGainer($gainer);

        /**
         * Handle update using available commands.
         */
        if ($command_signature = Telegram::commandSignature()) {
            foreach ($this->commands() as $command) {
                if ($command->signature === $command_signature) {
                    $command->handle($request, $gainer);

                    return;
                }
            }
        }

        /**
         * method that uses to handle update
         */
        $updated_handler_method = sprintf(
            'handle%s',
            Str::studly(Telegram::getUpdateType())
        );

        /**
         * Handle update using available breakers.
         */
        foreach ($this->breakers() as $breaker) {
			$method = $this->getMethod($breaker, $updated_handler_method);
			
            if ($method && $breaker->{$method}($request, $gainer)) {
				return;
            }
        }

        /**
         * Handle update using available handlers.
         */
        if ($gainer->handler) {
			$resolved_handler = try_resolve($gainer->handler);
			
            if ($method = $this->getMethod($resolved_handler, $updated_handler_method)) {
				$this->callHandlerMethod(
					$resolved_handler,
					$method,
					$request,
					$gainer
				);
			}
        }
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
	protected function getValidatedRequest($handler, string $method, Request $request): Request
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
	protected function callHandlerMethod($handler, string $method, Request $request, Model $gainer)
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
	
	public function getMethod(object $object, string $updateHandlerMethod): ?string
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
	 * @return Model
	 */
    abstract public function getGainer(Request $request): Model;

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
}
