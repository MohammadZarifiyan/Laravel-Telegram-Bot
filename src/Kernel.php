<?php

namespace App\Telegram;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use MohammadZarifiyan\Telegram\Facades\Telegram;

class Kernel extends \MohammadZarifiyan\Telegram\Abstractions\Kernel
{
    public function commands(): array
    {
        return [
            //
        ];
    }

    public function breakers(): array
    {
        return [
            //
        ];
    }

    public function getGainer(Request $request): ?Model
    {
        $user = Telegram::getUser();

        return User::firstOrCreate([
			'telegram_id' => $user->id
		]);
    }
}
