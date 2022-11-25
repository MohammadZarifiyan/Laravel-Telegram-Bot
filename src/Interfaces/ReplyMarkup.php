<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

interface ReplyMarkup
{
    public function __invoke(Request $request, Model $gainer): array;
}
