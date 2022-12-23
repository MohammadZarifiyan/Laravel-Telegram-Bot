<?php

namespace MohammadZarifiyan\Telegram;

if (!function_exists('try_resolve')) {
	function try_resolve($class)
	{
		if (empty($class)) {
			return null;
		}
		
		return is_object($class) ? $class : new $class;
	}
}
