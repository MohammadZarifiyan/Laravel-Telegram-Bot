<?php

namespace MohammadZarifiyan\Telegram\Abstractions;

use App\Models\User;
use Illuminate\Http\Request;

abstract class Command
{
    /**
     * The name and signature of the Telegram bot command.
     *
     * @var string
     */
    public string $signature;

    /**
     * Execute the Telegram command.
     */
    abstract public function handle(Request $request, User $user);
}
