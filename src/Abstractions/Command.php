<?php

namespace MohammadZarifiyan\Telegram\Abstractions;

use Illuminate\Database\Eloquent\Model;
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
    abstract public function handle(Request $request, ?Model $gainer);
}
