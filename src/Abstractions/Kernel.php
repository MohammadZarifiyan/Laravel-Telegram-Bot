<?php

namespace MohammadZarifiyan\Telegram\Abstractions;

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
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function handleUpdate(Request $request)
    {
        $gainer = $this->getGainer();

        /**
         * Handle update using available commands.
         */
        if ($text = $request->input('message.text')) {
            foreach ($this->commands() as $command) {
                $resolved_breaker = $this->container->make($command);

                if ($resolved_breaker->signature === substr($text, 1)) {
                    $resolved_breaker->handle($gainer);

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
            $resolved_breaker = $this->container->make($breaker);

            if (method_exists($resolved_breaker, $method) && $resolved_breaker->handle($gainer)) {
                return;
            }
        }

        /**
         * Handle update using available handlers.
         */
        if ($gainer->handler) {
            $resolved_handler = $this->container->make($gainer->handler);

            if (method_exists($resolved_handler, $method)) {
                $resolved_handler->{$method}($gainer);
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
