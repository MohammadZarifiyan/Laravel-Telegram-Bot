<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

use Illuminate\Http\Request;

interface RequestParser
{
	public function __construct(Request $request);
	
	public function getUpdateType(): ?string;
	
	public function getChatType(): ?string;
}
