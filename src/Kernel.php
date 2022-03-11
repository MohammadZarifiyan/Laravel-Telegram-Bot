<?php

namespace App\Telegram;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use MohammadZarifiyan\Telegram\Facades\Telegram;

class Kernel extends \MohammadZarifiyan\Telegram\Abstractions\Kernel
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
    public function breakers(): array
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
        $user = Telegram::getUser();

        return User::firstOrCreate([
			'telegram_id' => $user->id
		]);
    }
}
