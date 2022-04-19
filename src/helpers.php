<?php

use Illuminate\Support\Facades\App;

if (!function_exists('try_resolve')) {
	function try_resolve($class, $parameters = null)
	{
		if (!$class) {
			return null;
		}
		
		return is_object($class) ? $class : App::make($class, $parameters);
	}
}
