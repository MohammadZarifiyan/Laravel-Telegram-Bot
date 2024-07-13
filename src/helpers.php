<?php

namespace MohammadZarifiyan\Telegram;

use Closure;

if (!function_exists('try_resolve')) {
	function try_resolve($class)
	{
		if (empty($class)) {
			return null;
		}
		
		return is_object($class) ? $class : new $class;
	}
}


if (!function_exists('array_map_recursive')) {
    function array_map_recursive(array $array, Closure $callback): array
    {
        function recursive(array $array, Closure $callback, int $depth): array
        {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $array[$key] = recursive($value, $callback, $depth + 1);
                }
                else {
                    $array[$key] = call_user_func($callback, $value, $key, $depth);
                }
            }

            return $array;
        }

        return recursive($array, $callback, 0);
    }
}
