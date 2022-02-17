<?php

namespace MohammadZarifiyan\Telegram\Abstractions;

use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use MohammadZarifiyan\Telegram\Facades\Telegram;

abstract class Kernel
{
    public function __construct(public Container $container)
    {
        //
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
                $resolved_command = $this->container->make($command);

                if (
                    $resolved_command
                    && method_exists($command, 'handle')
                    && property_exists($command, 'signature')
                    && $resolved_command->signature === substr($text, 1))
                {
                    $resolved_command->handle($gainer);

                    return;
                }
            }
        }

        /**
         * Handle update using available handlers.
         */
        if ($gainer->handler) {
            $method = sprintf(
                'handle%s',
                Str::studly(Telegram::getUpdateType())
            );

            $resolved_handler = $this->container->make($gainer->handler);

            if ($resolved_handler && method_exists($resolved_handler, $method)) {
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
}
