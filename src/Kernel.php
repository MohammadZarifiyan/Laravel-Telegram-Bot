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
        $telegram_id = Telegram::getUser()->id;

        return User::where('telegram_id', '=', $telegram_id)->firstOr(function () use ($telegram_id) {
            $id = User::insertGetId([
                'telegram_id' => $telegram_id
            ]);

            return User::find($id);
        });
    }
}
