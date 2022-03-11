<?php

namespace MohammadZarifiyan\Telegram\Abstractions;

use Exception;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use MohammadZarifiyan\Telegram\Facades\Telegram;

abstract class Kernel
{
    public $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Handles incoming Telegram update.
     *
     * @param Request $request
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws Exception
     */
    public function handleUpdate(Request $request)
    {
        if (!Telegram::getUpdateType()) {
            throw new Exception('This request doesnt have any valid Telegram update.');
        }

        $gainer = $this->getGainer();

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
        $method = sprintf(
            'handle%s',
            Str::studly(Telegram::getUpdateType())
        );

        /**
         * Handle update using available breakers.
         */
        foreach ($this->breakers() as $breaker) {
            if (method_exists($breaker, $method) && $breaker->{$method}($request, $gainer)) {
                return;
            }
        }

        /**
         * Handle update using available handlers.
         */
        if ($gainer->handler) {
            $resolved_handler = is_object($gainer->handler) ? $gainer->handler : $this->container->make($gainer->handler);

            if (method_exists($resolved_handler, $method)) {
                $resolved_handler->{$method}($request, $gainer);
            }
        }
    }

    /**
     * Get or create the gainer that handlers should work with
     *
     * @return Model
     */
    abstract public function getGainer(): Model;

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
