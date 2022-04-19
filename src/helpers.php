<?php

use Illuminate\Support\Facades\App;

if (!function_exists('try_resolve')) {
	function try_resolve($class, $parameters = [])
	{
		if (!$class) {
			return null;
		}
		
		return is_object($class) ? $class : App::make($class, $parameters);
	}
}
