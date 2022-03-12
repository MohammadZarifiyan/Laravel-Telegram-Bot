<?php

namespace MohammadZarifiyan\Telegram;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use MohammadZarifiyan\Telegram\Exceptions\TelegramValidationException;

class TelegramRequest extends FormRequest
{
	public function failedValidation(Validator $validator)
	{
		throw new TelegramValidationException($validator);
	}
}
