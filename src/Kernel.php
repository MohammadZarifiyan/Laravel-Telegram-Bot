<?php

namespace MohammadZarifiyan\Telegram;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use MohammadZarifiyan\Telegram\Facades\Telegram;

class Kernel extends Abstractions\Kernel
{
    /**
     * @inheritDoc
     */
    public function commands(): array
    {
        return [
            //
        ];
    }

    /**
     * @inheritDoc
     */
    public function getGainer(): Model
    {
        return User::create(['telegram_id' => Telegram::getUser()->id]); // Assumes that User model uses TelegramGainer trait
    }
}
