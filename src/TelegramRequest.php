<?php

namespace MohammadZarifiyan\Telegram;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class TelegramRequest extends FormRequest
{
	public function failedValidation(Validator $validator)
	{
		throw (new ValidationException($validator))->errorBag($this->errorBag);
	}
}
