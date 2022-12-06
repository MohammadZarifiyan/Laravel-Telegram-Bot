<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface Response
{
	/**
	 * Telegram API method.
	 *
	 * @param Request $request
	 * @param Model|null $gainer
	 * @return string
	 */
    public function method(Request $request, ?Model $gainer): string;
	
	/**
	 * Data to use for sending response using Telegram API.
	 *
	 * @param Request $request
	 * @param Model|null $gainer
	 * @return array
	 */
    public function data(Request $request, ?Model $gainer): array;
}
