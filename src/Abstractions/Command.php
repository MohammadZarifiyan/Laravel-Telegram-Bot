<?php

namespace MohammadZarifiyan\Telegram\Abstractions;

use App\Models\User;

abstract class Command
{
    /**
     * The name and signature of the Telegram bot command.
     *
     * @var string
     */
    public $signature;

    /**
     * Execute the Telegram command.
     */
    abstract public function handle(User $user);
}
